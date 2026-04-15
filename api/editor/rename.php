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

$old_full  = safe_path($workspace, $input['old_path'] ?? '', true);
$new_full  = safe_path($workspace, $input['new_path'] ?? '');

if ($old_full === false || $new_full === false) {
    http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit;
}
if (file_exists($new_full)) {
    http_response_code(409); echo json_encode(['error' => 'Destination already exists']); exit;
}

rename($old_full, $new_full);
$rel = ltrim(str_replace(str_replace('\\', '/', $workspace), '', str_replace('\\', '/', $new_full)), '/');
echo json_encode(['success' => true, 'new_path' => $rel]);
