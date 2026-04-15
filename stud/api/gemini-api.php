<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then deny access
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

// File: /api/gemini-api.php
// Purpose: Securely handle requests to the Gemini API and save chat history.

// --- Initial Setup and Error Checking ---
header('Content-Type: application/json');

// This function will catch fatal errors and format them as JSON.
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'A fatal server error occurred.',
            'details' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

try {
    $config_path = realpath(__DIR__ . '/../config.php');
    if (!$config_path || !file_exists($config_path)) {
        throw new Exception('Configuration file not found. Please check the path.');
    }
    require_once($config_path);

    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'AIzaSyDkJXml4A_QqQ4yoLjB3xj31o8JllQVXe0' || empty(GEMINI_API_KEY)) {
        throw new Exception('Gemini API key is not configured in config.php.');
    }

    if (!$link) {
        throw new Exception('Database connection failed. Please check your config.php settings.');
    }

    // --- Main API Logic ---
    $user_id = $_SESSION["id"];
    $tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session
    $input = json_decode(file_get_contents('php://input'), true);
    $user_message = $input['message'] ?? '';
    $chat_id = $input['chat_id'] ?? null;

    if (empty($user_message)) {
        throw new Exception('Message cannot be empty.');
    }

    // --- Database Interaction: Save User Message and Get History ---
    // If no chat_id, create a new chat session first (for the current tenant)
    if (!$chat_id) {
        $chat_title = "Chat on " . date("Y-m-d H:i:s");
        $stmt = mysqli_prepare($link, "INSERT INTO ai_chats (user_id, tenant_id, title) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $tenant_id, $chat_title);
        mysqli_stmt_execute($stmt);
        $chat_id = mysqli_insert_id($stmt);
        mysqli_stmt_close($stmt);
        if (!$chat_id) {
            throw new Exception("Failed to create a new chat session.");
        }
    }

    // Before proceeding, verify the chat_id belongs to the user's tenant
    $verify_stmt = mysqli_prepare($link, "SELECT id FROM ai_chats WHERE id = ? AND tenant_id = ?");
    mysqli_stmt_bind_param($verify_stmt, "ii", $chat_id, $tenant_id);
    mysqli_stmt_execute($verify_stmt);
    mysqli_stmt_store_result($verify_stmt);
    if(mysqli_stmt_num_rows($verify_stmt) == 0){
        throw new Exception("Chat session not found or access denied.");
    }
    mysqli_stmt_close($verify_stmt);


    // Save the user's message
    $stmt = mysqli_prepare($link, "INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'user', ?)");
    mysqli_stmt_bind_param($stmt, "is", $chat_id, $user_message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Fetch context history
    $stmt = mysqli_prepare($link, "SELECT sender, message FROM chat_messages WHERE chat_id = ? ORDER BY created_at DESC LIMIT 10");
    mysqli_stmt_bind_param($stmt, "i", $chat_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = ['role' => $row['sender'], 'parts' => [['text' => $row['message']]]];
    }
    $history = array_reverse($history);
    mysqli_stmt_close($stmt);
    
    // --- Call Gemini API ---
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . GEMINI_API_KEY;
    $data = ['contents' => $history];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        throw new Exception('cURL Error: ' . $curl_error);
    }

    // --- Process API Response ---
    $response_data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode API response. Raw response: ' . $response);
    }
    
    $model_message = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($http_code !== 200) {
        $error_details = $response_data['error']['message'] ?? 'Unknown API error.';
        throw new Exception('API Error (' . $http_code . '): ' . $error_details);
    }
    
    if ($model_message === null) {
        // This handles cases where the API response is valid but contains no text, e.g., safety blocks.
        $model_message = "I'm sorry, but I can't provide a response to that.";
    }

    // --- Save Model's Response to Database ---
    $stmt = mysqli_prepare($link, "INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'model', ?)");
    mysqli_stmt_bind_param($stmt, "is", $chat_id, $model_message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // --- Send Success Response to Frontend ---
    echo json_encode(['reply' => $model_message, 'chat_id' => $chat_id]);

} catch (Exception $e) {
    // Catch any exceptions and format them as a standard JSON error
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>