<?php
// File: api/editor/browse-dirs.php
// Purpose: Lists subdirectories at a given path — powers the folder-picker UI.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once(__DIR__ . '/../../config.php');

$raw  = trim($_GET['path'] ?? $_SERVER['DOCUMENT_ROOT']);
$path = str_replace('\\', '/', $raw);
$real = realpath($path);

if ($real === false || !is_dir($real)) {
    echo json_encode(['error' => 'Invalid path', 'dirs' => [], 'files' => []]);
    exit;
}

$dirs  = [];
$files = [];
$entries = @scandir($real);

if ($entries) {
    foreach (array_diff($entries, ['.', '..']) as $entry) {
        $full = $real . DIRECTORY_SEPARATOR . $entry;
        // Skip hidden / system items
        if (str_starts_with($entry, '.')) continue;
        if (is_dir($full)) {
            $dirs[] = $entry;
        } else {
            $files[] = $entry;
        }
    }
    sort($dirs);
    sort($files);
}

// Build breadcrumb from the real path
$normalised = str_replace('\\', '/', $real);
$parts      = array_filter(explode('/', $normalised));
$crumbs     = [];
$acc        = '';
foreach ($parts as $part) {
    // Rebuild correctly for both Unix (/foo/bar) and Windows (C:/foo/bar)
    $acc .= ($acc === '' || str_ends_with($acc, ':')) ? $part : '/' . $part;
    // For Unix root, keep leading /
    if (str_starts_with($normalised, '/') && !str_starts_with($acc, '/')) {
        $acc = '/' . $acc;
    }
    $crumbs[] = ['label' => $part ?: '/', 'path' => $acc];
}

echo json_encode([
    'path'     => $normalised,
    'parent'   => str_replace('\\', '/', dirname($real)),
    'crumbs'   => $crumbs,
    'dirs'     => $dirs,
    'files'    => $files,
    'writable' => is_writable($real),
]);
