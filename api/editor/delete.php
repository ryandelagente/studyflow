<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

require_once(__DIR__ . '/../../config.php');

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403); echo json_encode(['error' => 'CSRF validation failed']); exit;
}

require_once(__DIR__ . '/_workspace.php');

$input     = json_decode(file_get_contents('php://input'), true);
$workspace = get_workspace_root();
$full      = safe_path($workspace, $input['path'] ?? '', true);

if ($full === false) {
    http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit;
}

function delete_recursive(string $path): void {
    if (is_dir($path)) {
        foreach (array_diff(scandir($path), ['.', '..']) as $item) {
            delete_recursive($path . DIRECTORY_SEPARATOR . $item);
        }
        rmdir($path);
    } else {
        unlink($path);
    }
}

delete_recursive($full);
echo json_encode(['success' => true]);
