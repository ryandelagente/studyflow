<?php
// File: api/update-plan.php
// Purpose: Update the current tenant's membership plan.
//          Only accessible to admin or super_admin.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
}
if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    http_response_code(403); echo json_encode(['error' => 'Admin access required']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403); echo json_encode(['error' => 'CSRF validation failed']); exit;
}

require_once(__DIR__ . '/../config.php');

if (!$link) {
    http_response_code(500); echo json_encode(['error' => 'Database unavailable']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$plan  = trim($input['plan'] ?? '');

$valid_plans = ['free', 'basic', 'standard', 'premium'];
if (!in_array($plan, $valid_plans)) {
    http_response_code(400); echo json_encode(['error' => 'Invalid plan slug']); exit;
}

$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
if ($tenant_id < 1) {
    http_response_code(400); echo json_encode(['error' => 'No workspace found for this account']); exit;
}

$stmt = mysqli_prepare($link, "UPDATE tenants SET plan = ?, status = 'active' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $plan, $tenant_id);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'plan' => $plan]);
} else {
    mysqli_stmt_close($stmt);
    http_response_code(500);
    echo json_encode(['error' => 'Could not update plan']);
}
