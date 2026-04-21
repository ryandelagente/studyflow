<?php
// File: api/editor/ai-agent.php
// Purpose: Agentic AI endpoint — receives full workspace context, returns explanation
//          + structured file operations (create / edit / delete).

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
require_once(__DIR__ . '/_workspace.php');

if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenRouter API key is not configured. Go to Settings → API.']);
    exit;
}

// ── Input ─────────────────────────────────────────────────────────────────────
$input   = json_decode(file_get_contents('php://input'), true);
$task    = trim($input['task']    ?? '');
$context = $input['context']      ?? [];   // array of {path, content}

if (empty($task)) {
    http_response_code(400);
    echo json_encode(['error' => 'Task is required']);
    exit;
}

// Guard against huge payloads
if (strlen(json_encode($context)) > 600_000) {
    http_response_code(400);
    echo json_encode(['error' => 'Workspace context is too large. Try limiting workspace size or reloading.']);
    exit;
}

// ── Build codebase context string ────────────────────────────────────────────
$workspace      = get_workspace_root();
$workspace_label = basename($workspace);

$context_parts = [];
foreach ($context as $file) {
    $path = $file['path'] ?? '';
    if (!$path) continue;

    if (!empty($file['note'])) {
        // File was too large / skipped
        $context_parts[] = "=== FILE: {$path} ===\n[{$file['note']}]\n";
    } elseif (isset($file['content']) && $file['content'] !== null) {
        $context_parts[] = "=== FILE: {$path} ===\n{$file['content']}\n";
    }
}

$context_string = implode("\n", $context_parts);

// Hard cap: truncate context string at 80 000 chars to stay within model limits
if (mb_strlen($context_string) > 80_000) {
    $context_string = mb_substr($context_string, 0, 80_000) . "\n\n[... context truncated at 80 000 characters ...]";
}

// ── System prompt ─────────────────────────────────────────────────────────────
$system_prompt = <<<'SYSTEM'
You are an expert full-stack developer and AI coding agent embedded inside a browser IDE called StudyFlow Code Editor.

The user has loaded their workspace and will give you a task. You can read ALL the files in the workspace (provided below). Your job is to implement features, fix bugs, refactor code, write new files, or analyze the codebase — whatever the task requires.

═══════════════════════════════════════════════
OUTPUT FORMAT — READ THIS CAREFULLY
═══════════════════════════════════════════════

When your response requires creating or modifying files, you MUST include a changes block in this EXACT format at the END of your response:

===FILE_CHANGES_START===
[
  {"action": "create", "path": "path/to/new-file.php", "content": "complete file content here"},
  {"action": "edit",   "path": "path/to/existing.php", "content": "complete updated content"},
  {"action": "delete", "path": "path/to/remove.php"}
]
===FILE_CHANGES_END===

RULES FOR FILE CHANGES:
1. "action" must be exactly "create", "edit", or "delete"
2. For "create" and "edit" — the "content" field MUST contain the COMPLETE file content, not a diff or excerpt
3. For "delete" — omit the "content" field
4. Paths are relative to the workspace root, use forward slashes
5. If no file changes are needed (e.g. for analysis questions), omit the block entirely
6. The JSON inside the block must be valid — no trailing commas, no comments
7. Do NOT wrap the JSON in markdown code fences inside the block
8. Place your explanation BEFORE the ===FILE_CHANGES_START=== marker

CODING RULES:
- Follow the exact same code style, patterns, and architecture as the existing codebase
- For PHP: match existing indentation, use the same DB connection variable ($link), follow the same CSRF / session patterns
- Write fully working code — no placeholder comments like "// add logic here" unless the user explicitly asked for a skeleton
- If a database table or migration is needed, include a .sql file in the changes
- Validate and sanitize all user input; use prepared statements for DB queries
- Think step by step before writing any code

SYSTEM;

// ── Assemble user message ─────────────────────────────────────────────────────
$user_message = "WORKSPACE: {$workspace_label}\n\nTASK: {$task}\n\n";
if ($context_string) {
    $user_message .= "=== WORKSPACE FILES ===\n\n{$context_string}";
} else {
    $user_message .= "(No workspace files loaded — working from task description only)";
}

// ── Call OpenRouter ──────────────────────────────────────────────────────────
$messages = [
    ['role' => 'system', 'content' => $system_prompt],
    ['role' => 'user',   'content' => $user_message],
];

$payload = json_encode([
    'model'       => OPENROUTER_MODEL,
    'messages'    => $messages,
    'max_tokens'  => 8192,   // agents produce complete files — need more tokens
    'temperature' => 0.2,    // lower temp = more consistent, less hallucination
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
        'X-Title: StudyFlow AI Agent',
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 120,   // agent tasks take longer
]);

$response  = curl_exec($ch);
$curl_err  = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
        'error' => 'AI returned an unexpected response (HTTP ' . $http_code . ').',
        'debug' => substr($response, 0, 500),
    ]);
    exit;
}

$raw_content = $data['choices'][0]['message']['content'];

// ── Parse file changes ────────────────────────────────────────────────────────
$changes      = [];
$parse_errors = [];
$message      = $raw_content;

if (preg_match('/===FILE_CHANGES_START===\s*([\s\S]*?)\s*===FILE_CHANGES_END===/m', $raw_content, $m)) {
    $json_block = trim($m[1]);

    // Strip markdown code fences the AI might add (```json ... ```)
    $json_block = preg_replace('/^```(?:json)?\s*/m', '', $json_block);
    $json_block = preg_replace('/```\s*$/m', '',        $json_block);
    $json_block = trim($json_block);

    $parsed = json_decode($json_block, true);

    if (is_array($parsed)) {
        // Validate each change — security-critical: reject paths outside workspace
        foreach ($parsed as $change) {
            $action  = $change['action'] ?? '';
            $path    = $change['path']   ?? '';
            $content = $change['content'] ?? '';

            if (!in_array($action, ['create', 'edit', 'delete'])) {
                $parse_errors[] = "Unknown action '{$action}' — skipped";
                continue;
            }
            if (empty($path)) {
                $parse_errors[] = "Empty path — skipped";
                continue;
            }

            // Security: validate path stays inside workspace
            $safe = safe_path($workspace, $path);
            if ($safe === false) {
                $parse_errors[] = "Unsafe path '{$path}' — blocked";
                continue;
            }

            // Normalise path to forward slashes, strip leading slash
            $norm_path = ltrim(str_replace('\\', '/', $path), '/');

            $changes[] = [
                'action'  => $action,
                'path'    => $norm_path,
                'content' => ($action !== 'delete') ? $content : null,
            ];
        }
    } else {
        $parse_errors[] = 'AI returned malformed JSON in the changes block — no changes applied.';
    }

    // Remove the changes block from the displayed message
    $message = trim(preg_replace(
        '/===FILE_CHANGES_START===[\s\S]*?===FILE_CHANGES_END===/m',
        '',
        $raw_content
    ));
}

// ── Respond ───────────────────────────────────────────────────────────────────
echo json_encode([
    'message' => $message,
    'changes' => $changes,
    'errors'  => $parse_errors,
    'usage'   => $data['usage'] ?? null,
]);
