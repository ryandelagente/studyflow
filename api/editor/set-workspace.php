<?php
// File: api/editor/set-workspace.php
// Purpose: Sets (or clears) the editor workspace to a local folder path.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once(__DIR__ . '/../../config.php');

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF validation failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$path  = trim($input['path'] ?? '');

// Clear workspace → fall back to uploads/code/
if ($path === '') {
    unset($_SESSION['editor_workspace']);
    echo json_encode(['success' => true, 'workspace' => null, 'label' => 'Default workspace']);
    exit;
}

// Normalise Windows back-slashes
$path = str_replace('\\', '/', $path);
$real = realpath($path);

if ($real === false || !is_dir($real)) {
    echo json_encode(['error' => 'Path does not exist or is not a directory: ' . htmlspecialchars($path)]);
    exit;
}
if (!is_readable($real)) {
    echo json_encode(['error' => 'Directory is not readable by the web server']);
    exit;
}

$_SESSION['editor_workspace'] = $real;

// Count top-level items for feedback
$items = array_diff(scandir($real), ['.', '..']);
echo json_encode([
    'success'   => true,
    'workspace' => str_replace('\\', '/', $real),
    'label'     => basename($real),
    'items'     => count($items),
]);
