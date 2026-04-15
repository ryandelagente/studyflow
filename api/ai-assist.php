<?php
// File: api/ai-assist.php
// Purpose: Reusable one-shot AI endpoint for all modules (no chat history).

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// CSRF check for fetch-based calls (token sent as header or in body)
$csrf_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrf_ok = !empty($csrf_header) && hash_equals($_SESSION['csrf_token'] ?? '', $csrf_header);
if (!$csrf_ok) {
    http_response_code(403);
    echo json_encode(['error' => 'Security token invalid.']);
    exit;
}

require_once(__DIR__ . '/../config.php');

if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenRouter API key is not configured.']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$feature = $input['feature'] ?? '';
$context = trim($input['context'] ?? '');
$count   = max(1, min(20, (int)($input['count'] ?? 5)));

if (empty($feature) || empty($context)) {
    http_response_code(400);
    echo json_encode(['error' => 'Feature and context are required.']);
    exit;
}

$system_prompts = [
    'notes_write' => 'You are a helpful academic writing assistant. Write clear, well-structured HTML content suitable for a rich text note editor. Use <h2>, <p>, <ul>, <strong> tags as appropriate. Be thorough and informative.',
    'notes_summarize' => 'You are a concise summarizer. Summarize the provided text into clear key bullet points using HTML <ul><li> tags. Focus on the most important information.',
    'flashcards_generate' => 'You are a flashcard generator. Output ONLY a raw JSON array — no markdown, no code fences, no explanation, no text before or after. Generate exactly ' . $count . ' items. Each item must use double-quoted keys and string values. Example of the exact format required: [{"question":"What is X?","answer":"X is Y."},{"question":"Define Z.","answer":"Z means W."}]',
    'assignment_describe' => 'You are an academic assistant. Write a clear, detailed, and professional assignment description for a student. Include objectives, requirements, and expected deliverables. Return plain text (no HTML).',
    'goals_suggest' => 'You are a study coach. Output ONLY a raw JSON array — no markdown, no code fences, no explanation, no text before or after. Suggest exactly ' . $count . ' items. Each item must use double-quoted keys and string values. Example of the exact format required: [{"title":"Goal title","description":"What to achieve and by when."}]',
    'todos_generate' => 'You are a task planning assistant. Output ONLY a raw JSON array — no markdown, no code fences, no explanation, no text before or after. Generate exactly ' . $count . ' items. Each item must use double-quoted keys and string values. Priority must be HIGH, MEDIUM, or LOW. Example of the exact format required: [{"title":"Task name","priority":"HIGH"},{"title":"Another task","priority":"MEDIUM"}]',
];

if (!isset($system_prompts[$feature])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown feature: ' . $feature]);
    exit;
}

$payload = json_encode([
    'model'    => OPENROUTER_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $system_prompts[$feature]],
        ['role' => 'user',   'content' => $context],
    ],
]);

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENROUTER_API_KEY,
    'HTTP-Referer: ' . BASE_URL,
    'X-Title: StudyFlow',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL Error: ' . $curl_err]);
    exit;
}

$data = json_decode($response, true);

if ($http_code !== 200) {
    $msg = $data['error']['message'] ?? 'Unknown API error.';
    http_response_code(500);
    echo json_encode(['error' => 'API Error (' . $http_code . '): ' . $msg]);
    exit;
}

$result = $data['choices'][0]['message']['content'] ?? '';

// Strip markdown code fences (```json ... ``` or ``` ... ```)
$result = preg_replace('/^```(?:json)?\s*/i', '', trim($result));
$result = preg_replace('/\s*```\s*$/i', '', $result);
$result = trim($result);

// For JSON-returning features, extract and repair the array
$json_features = ['flashcards_generate', 'goals_suggest', 'todos_generate'];
if (in_array($feature, $json_features)) {
    // Extract the outermost [...] block (ignore any surrounding prose)
    $start = strpos($result, '[');
    $end   = strrpos($result, ']');
    if ($start !== false && $end !== false && $end > $start) {
        $result = substr($result, $start, $end - $start + 1);
    }

    // Attempt to repair common AI JSON mistakes before parsing
    $result = repairJson($result);

    $decoded = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'AI returned malformed JSON. Try again.', 'debug' => substr($result, 0, 300)]);
        exit;
    }
    $result = json_encode($decoded); // Re-encode to normalise
}

echo json_encode(['result' => $result]);

// ---------------------------------------------------------------------------
// Repair common JSON mistakes AI models make
// ---------------------------------------------------------------------------
function repairJson(string $json): string {
    // 1. Replace curly/smart quotes with straight double quotes
    $json = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], '"', $json);

    // 2. Remove trailing commas before ] or } (e.g. [1,2,3,] or {"a":1,})
    $json = preg_replace('/,\s*([\]\}])/', '$1', $json);

    // 3. Replace single-quoted strings with double-quoted (simple heuristic)
    //    Only do this if the string doesn't already look like valid JSON
    if (json_decode($json) === null) {
        $json = preg_replace("/(?<![\\\\])'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/", '"$1"', $json);
    }

    // 4. Remove any control characters inside string values that break JSON
    $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json);

    return $json;
}
