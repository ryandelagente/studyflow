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
$type      = ($input['type'] ?? 'file') === 'dir' ? 'dir' : 'file';
$full      = safe_path($workspace, $input['path'] ?? '');

if ($full === false) {
    http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit;
}
if (file_exists($full)) {
    http_response_code(409); echo json_encode(['error' => 'Already exists']); exit;
}

if ($type === 'dir') {
    mkdir($full, 0755, true);
} else {
    file_put_contents($full, '');
}

$rel = str_replace(str_replace('\\', '/', $workspace) . '/', '', str_replace('\\', '/', $full));
echo json_encode(['success' => true, 'path' => $rel, 'type' => $type]);
