<?php
// File: /api/gemini-api.php
// Purpose: Handle AI chat requests via OpenRouter API and save chat history.

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
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

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'A fatal server error occurred.', 'details' => $error['message']]);
    }
});

try {
    require_once(realpath(__DIR__ . '/../config.php'));

    if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
        throw new Exception('OpenRouter API key is not configured. Add it to secrets.php.');
    }

    if (!$link) {
        throw new Exception('Database connection failed. Please check your config.php settings.');
    }

    $user_id   = (int)$_SESSION["id"];
    $tenant_id = $_SESSION["tenant_id"] ?? null;

    if ($tenant_id === null) {
        throw new Exception('Tenant ID is missing. Please log out and log back in to refresh your session.');
    }
    $tenant_id = (int)$tenant_id;

    $input        = json_decode(file_get_contents('php://input'), true);
    $user_message = trim($input['message'] ?? '');
    $chat_id      = $input['chat_id'] ?? null;

    if (empty($user_message)) {
        throw new Exception('Message cannot be empty.');
    }

    // Create a new chat session if none exists
    if (!$chat_id) {
        $chat_title = "Chat on " . date("Y-m-d H:i:s");
        $stmt = mysqli_prepare($link, "INSERT INTO ai_chats (user_id, tenant_id, title) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $tenant_id, $chat_title);
        mysqli_stmt_execute($stmt);
        $chat_id = mysqli_insert_id($link);
        mysqli_stmt_close($stmt);
        if (!$chat_id) {
            throw new Exception("Failed to create a new chat session.");
        }
    }

    // Verify the chat belongs to this user's tenant
    $verify = mysqli_prepare($link, "SELECT id FROM ai_chats WHERE id = ? AND tenant_id = ?");
    mysqli_stmt_bind_param($verify, "ii", $chat_id, $tenant_id);
    mysqli_stmt_execute($verify);
    mysqli_stmt_store_result($verify);
    if (mysqli_stmt_num_rows($verify) == 0) {
        throw new Exception("Chat session not found or access denied.");
    }
    mysqli_stmt_close($verify);

    // Save the user's message
    $stmt = mysqli_prepare($link, "INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'user', ?)");
    mysqli_stmt_bind_param($stmt, "is", $chat_id, $user_message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Fetch recent chat history for context
    $stmt = mysqli_prepare($link, "SELECT sender, message FROM chat_messages WHERE chat_id = ? ORDER BY created_at DESC LIMIT 10");
    mysqli_stmt_bind_param($stmt, "i", $chat_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // OpenRouter uses 'assistant' instead of 'model'
        $role      = ($row['sender'] === 'model') ? 'assistant' : 'user';
        $history[] = ['role' => $role, 'content' => $row['message']];
    }
    $history = array_reverse($history);
    mysqli_stmt_close($stmt);

    // Call OpenRouter API (OpenAI-compatible format)
    $api_url = 'https://openrouter.ai/api/v1/chat/completions';
    $payload = json_encode([
        'model'    => OPENROUTER_MODEL,
        'messages' => $history,
    ]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENROUTER_API_KEY,
        'HTTP-Referer: ' . BASE_URL,
        'X-Title: StudyFlow AI Tutor',
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        throw new Exception('cURL Error: ' . $curl_err);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode API response. Raw: ' . $response);
    }

    if ($http_code !== 200) {
        $msg = $data['error']['message'] ?? 'Unknown API error.';
        throw new Exception('API Error (' . $http_code . '): ' . $msg);
    }

    $model_message = $data['choices'][0]['message']['content'] ?? null;
    if ($model_message === null) {
        $model_message = "I'm sorry, I couldn't generate a response. Please try again.";
    }

    // Save the model's reply
    $stmt = mysqli_prepare($link, "INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'model', ?)");
    mysqli_stmt_bind_param($stmt, "is", $chat_id, $model_message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo json_encode(['reply' => $model_message, 'chat_id' => $chat_id]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
