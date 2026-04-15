<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then deny access
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

// File: /api/load-chat.php
// Purpose: Fetches the message history for a given chat ID within a tenant.

header('Content-Type: application/json');
require_once(__DIR__ . '/../config.php');

// --- SAAS FIX: Use tenant_id from session ---
$tenant_id = $_SESSION["tenant_id"];

$input = json_decode(file_get_contents('php://input'), true);
$chat_id = $input['chat_id'] ?? 0;

if (!$link || $chat_id == 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request. Missing chat ID.']);
    exit;
}

$messages = [];
// Verify the user's tenant owns this chat before fetching messages
$sql = "SELECT m.sender, m.message FROM chat_messages m JOIN ai_chats c ON m.chat_id = c.id WHERE c.id = ? AND c.tenant_id = ? ORDER BY m.created_at ASC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $chat_id, $tenant_id);
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