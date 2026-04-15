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

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403); echo json_encode(['error' => 'CSRF validation failed']); exit;
}

require_once(__DIR__ . '/_workspace.php');

$workspace = get_workspace_root();

if (!isset($_FILES['files'])) {
    http_response_code(400); echo json_encode(['error' => 'No files received']); exit;
}

$files    = $_FILES['files'];
$paths    = $_POST['paths'] ?? [];
$uploaded = 0;
$errors   = [];
$count    = is_array($files['name']) ? count($files['name']) : 1;

for ($i = 0; $i < $count; $i++) {
    $tmp  = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
    $name = is_array($files['name'])     ? $files['name'][$i]     : $files['name'];
    $err  = is_array($files['error'])    ? $files['error'][$i]    : $files['error'];
    $rel  = isset($paths[$i]) && $paths[$i] !== '' ? $paths[$i] : $name;

    if ($err !== UPLOAD_ERR_OK) { $errors[] = "Upload error: $name"; continue; }

    // Clean path
    $parts = array_filter(explode('/', str_replace('\\', '/', $rel)), fn($p) => $p !== '..' && $p !== '.' && $p !== '');
    $rel   = implode('/', $parts);
    if ($rel === '') { $errors[] = "Invalid path: $name"; continue; }

    $dest = safe_path($workspace, $rel);
    if ($dest === false) { $errors[] = "Path escapes workspace: $name"; continue; }

    if (move_uploaded_file($tmp, $dest)) {
        $uploaded++;
    } else {
        $errors[] = "Failed to save: $name";
    }
}

echo json_encode(['uploaded' => $uploaded, 'errors' => $errors]);
