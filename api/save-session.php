<?php
// File: api/save-session.php
// Purpose: Saves a completed study timer session to the database.

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

$input = json_decode(file_get_contents('php://input'), true);
$duration_seconds = isset($input['duration_seconds']) ? (int)$input['duration_seconds'] : 0;

if ($duration_seconds <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid duration']);
    exit;
}

require_once(__DIR__ . '/../config.php');

$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

$sql = "INSERT INTO study_sessions (user_id, tenant_id, duration_seconds) VALUES (?, ?, ?)";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, 'iii', $user_id, $tenant_id, $duration_seconds);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

mysqli_close($link);
