<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/_workspace.php');

$workspace = get_workspace_root();
$rel_path  = $_GET['path'] ?? '';
$full      = safe_path($workspace, $rel_path, true);

if ($full === false || !is_file($full)) {
    http_response_code(404); echo json_encode(['error' => 'File not found']); exit;
}
if (filesize($full) > 2 * 1024 * 1024) {
    http_response_code(413); echo json_encode(['error' => 'File too large to edit in browser (> 2 MB)']); exit;
}

// Detect if binary
$content = file_get_contents($full);
if (!mb_check_encoding($content, 'UTF-8') && !mb_check_encoding($content, 'ASCII')) {
    echo json_encode(['error' => 'Binary file — cannot display in editor', 'binary' => true]); exit;
}

echo json_encode(['content' => $content]);
