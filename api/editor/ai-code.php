<?php
// File: api/editor/ai-code.php
// Purpose: AI coding assistant — answers questions about code, explains, fixes, or completes it.

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

$csrf_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_header)) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF validation failed']);
    exit;
}

require_once(__DIR__ . '/../../config.php');

if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenRouter API key is not configured. Go to Settings → API.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$action   = $input['action']   ?? 'chat';     // chat | explain | fix | complete | review
$question = trim($input['question'] ?? '');
$code     = $input['code']     ?? '';
$language = $input['language'] ?? 'plaintext';
$selected = $input['selected'] ?? '';         // highlighted selection (optional)

// Build system prompt based on action
$system_prompts = [
    'chat' => "You are an expert coding assistant. The user is working in a {$language} file. Answer their questions clearly and concisely. When providing code examples use proper markdown code fences.",
    'explain' => "You are a code explainer. Explain the provided {$language} code in plain English. Break it down step by step. Be clear and educational.",
    'fix' => "You are a code debugging expert. Analyse the provided {$language} code, identify bugs, errors, or issues, and provide the corrected code with an explanation of what was wrong.",
    'complete' => "You are an AI code completion assistant. Continue or complete the provided {$language} code naturally. Provide only the code that should come next, followed by a brief explanation.",
    'review' => "You are a senior code reviewer. Review the provided {$language} code for: correctness, performance, security issues, readability, and best practices. Give structured, actionable feedback.",
];

$system_msg = strtr($system_prompts[$action] ?? $system_prompts['chat'], ['{language}' => $language]);

// Build the user message
$target_code = ($selected !== '') ? $selected : $code;
$user_parts  = [];

if ($target_code !== '') {
    $user_parts[] = "```{$language}\n{$target_code}\n```";
}
if ($question !== '') {
    $user_parts[] = $question;
}
if (empty($user_parts)) {
    http_response_code(400);
    echo json_encode(['error' => 'No code or question provided']);
    exit;
}
// If selected code exists and full code differs, add full context
if ($selected !== '' && $code !== '' && $selected !== $code) {
    $system_msg .= "\n\nFull file context (for reference):\n```{$language}\n" . mb_substr($code, 0, 4000) . "\n```";
}

$user_message = implode("\n\n", $user_parts);

$messages = [
    ['role' => 'system', 'content' => $system_msg],
    ['role' => 'user',   'content' => $user_message],
];

$payload = json_encode([
    'model'       => OPENROUTER_MODEL,
    'messages'    => $messages,
    'max_tokens'  => 2048,
    'temperature' => 0.3,
]);

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENROUTER_API_KEY,
        'HTTP-Referer: ' . BASE_URL,
        'X-Title: StudyFlow Code Editor',
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 60,
]);

$response = curl_exec($ch);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    http_response_code(502);
    echo json_encode(['error' => 'Network error: ' . $curl_err]);
    exit;
}

$data = json_decode($response, true);
if (!isset($data['choices'][0]['message']['content'])) {
    http_response_code(502);
    echo json_encode([
        'error' => 'AI returned an unexpected response.',
        'debug' => substr($response, 0, 300),
    ]);
    exit;
}

echo json_encode(['response' => $data['choices'][0]['message']['content']]);
