<?php
// File: /api/load-chat.php
// Purpose: Fetches the message history for a given chat ID.

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}
header('Content-Type: application/json');
require_once(__DIR__ . '/../config.php');
$csrf_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($csrf_header) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_header)) {
    http_response_code(403);
    echo json_encode(['error' => 'Security token invalid.']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$chat_id = $input['chat_id'] ?? 0;
$user_id = (int)$_SESSION['id'];

if (!$link || $chat_id == 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request. Missing chat ID.']);
    exit;
}

$messages = [];
// Verify the user owns this chat before fetching messages
$sql = "SELECT m.sender, m.message FROM chat_messages m JOIN ai_chats c ON m.chat_id = c.id WHERE c.id = ? AND c.user_id = ? ORDER BY m.created_at ASC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $chat_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'sender' => $row['sender'],
            'message' => $row['message']
        ];
    }
    mysqli_stmt_close($stmt);
    echo json_encode($messages);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement.']);
}

?>

