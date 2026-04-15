<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/_workspace.php');

$workspace = get_workspace_root();

function build_tree(string $dir, string $base): array {
    $items   = [];
    $entries = @scandir($dir);
    if (!$entries) return [];
    foreach (array_diff($entries, ['.', '..']) as $entry) {
        $full = $dir . DIRECTORY_SEPARATOR . $entry;
        $rel  = $base ? $base . '/' . $entry : $entry;
        if (is_dir($full)) {
            $items[] = ['name' => $entry, 'type' => 'dir',  'path' => $rel, 'children' => build_tree($full, $rel)];
        } else {
            $items[] = ['name' => $entry, 'type' => 'file', 'path' => $rel, 'size' => filesize($full)];
        }
    }
    usort($items, fn($a, $b) => $a['type'] !== $b['type'] ? ($a['type'] === 'dir' ? -1 : 1) : strcmp($a['name'], $b['name']));
    return $items;
}

echo json_encode([
    'tree'      => build_tree($workspace, ''),
    'workspace' => str_replace('\\', '/', $workspace),
    'label'     => basename($workspace),
]);
