<?php
// File: api/get-free-models.php
// Purpose: Fetch the latest free models available on OpenRouter.
//          Called by the API Settings page (super_admin only).

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
}
if ($_SESSION['role'] !== 'super_admin') {
    http_response_code(403); echo json_encode(['error' => 'Super admin access required']); exit;
}

require_once(__DIR__ . '/../config.php');

// ── Fetch model list from OpenRouter (public endpoint, no auth needed) ──
$ch = curl_init('https://openrouter.ai/api/v1/models');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'HTTP-Referer: ' . BASE_URL,
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 20,
]);

$response  = curl_exec($ch);
$curl_err  = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_err) {
    http_response_code(502);
    echo json_encode(['error' => 'Network error: ' . $curl_err]); exit;
}

$data = json_decode($response, true);
if (!isset($data['data']) || !is_array($data['data'])) {
    http_response_code(502);
    echo json_encode(['error' => "Unexpected response from OpenRouter (HTTP $http_code)"]); exit;
}

// ── Filter free models ────────────────────────────────────
// A model is free if its id ends with ":free" OR both prompt and completion pricing are "0"
$free = [];
foreach ($data['data'] as $m) {
    $id     = $m['id'] ?? '';
    $prompt = (string)($m['pricing']['prompt']     ?? '1');
    $comp   = (string)($m['pricing']['completion'] ?? '1');

    $is_free = str_ends_with($id, ':free') || ($prompt === '0' && $comp === '0');
    if (!$is_free || !$id) continue;

    $context_k = isset($m['context_length']) ? round($m['context_length'] / 1000) . 'K' : '?';

    $free[] = [
        'id'          => $id,
        'name'        => $m['name'] ?? $id,
        'context'     => $context_k,
        'description' => isset($m['description'])
                            ? mb_substr(strip_tags($m['description']), 0, 120)
                            : '',
    ];
}

// Sort alphabetically by id
usort($free, fn($a, $b) => strcmp($a['id'], $b['id']));

echo json_encode([
    'models' => $free,
    'total'  => count($free),
    'fetched_at' => date('Y-m-d H:i:s'),
]);
