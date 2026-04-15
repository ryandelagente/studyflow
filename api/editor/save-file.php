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

$input    = json_decode(file_get_contents('php://input'), true);
$workspace = get_workspace_root();
$full     = safe_path($workspace, $input['path'] ?? '');
$content  = $input['content'] ?? '';

if ($full === false) {
    http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit;
}

if (file_put_contents($full, $content) === false) {
    http_response_code(500); echo json_encode(['error' => 'Could not write file — check permissions']); exit;
}

echo json_encode(['success' => true]);
