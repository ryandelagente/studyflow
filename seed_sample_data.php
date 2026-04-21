<?php
// ============================================================
// File: seed_sample_data.php  (project root)
// Purpose: One-time sample data seeder — run once via browser
//          or CLI: php seed_sample_data.php
// DELETE THIS FILE after running it in production!
// ============================================================

// Simple CLI / browser protection — only run locally
if (isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403); die('Forbidden — run locally only.');
}

require_once __DIR__ . '/config.php';

if (!$link) die('DB connection failed');

// Confirm prompt in browser
if (!isset($_GET['confirm']) && php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Seed Sample Data</title>
    <style>body{font-family:sans-serif;max-width:600px;margin:3rem auto;padding:0 1rem;}
    .warn{background:#fff3cd;border:1px solid #ffc107;padding:1rem;border-radius:6px;margin-bottom:1.5rem;}
    .btn{display:inline-block;padding:.6rem 1.5rem;border-radius:6px;text-decoration:none;font-weight:700;}
    .btn-go{background:#7c3aed;color:#fff;}.btn-go:hover{background:#6d28d9;}
    .btn-cancel{background:#e5e7eb;color:#374151;margin-left:.5rem;}
    </style></head>
    <body>
    <h2>🌱 Seed Sample Data</h2>
    <div class="warn">⚠ This will INSERT sample users and tenants. Existing records are unaffected.<br>
    Run the <code>sql/add_super_admin_membership.sql</code> migration first!</div>
    <p>The following accounts will be created (password: <strong>Test@1234</strong>):</p>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;font-size:.85rem;">
    <tr style="background:#f3f4f6;"><th>Name</th><th>Email</th><th>Role</th><th>Plan</th></tr>
    <tr><td>Super Admin</td><td>superadmin@studyflow.com</td><td>super_admin</td><td>—</td></tr>
    <tr><td>Alex Free</td><td>alex@example.com</td><td>admin</td><td>free</td></tr>
    <tr><td>Beth Basic</td><td>beth@example.com</td><td>admin</td><td>basic</td></tr>
    <tr><td>Carlos Standard</td><td>carlos@example.com</td><td>admin</td><td>standard</td></tr>
    <tr><td>Diana Premium</td><td>diana@example.com</td><td>admin</td><td>premium</td></tr>
    <tr><td>Eve User</td><td>eve@example.com</td><td>user</td><td>(under Carlos's workspace)</td></tr>
    <tr><td>Frank User</td><td>frank@example.com</td><td>user</td><td>(under Diana's workspace)</td></tr>
    </table>
    <br>
    <a href="?confirm=1" class="btn btn-go">▶ Run Seeder</a>
    <a href="index.php" class="btn btn-cancel">Cancel</a>
    </body></html>
    <?php
    exit;
}

// ── Helper ────────────────────────────────────────────────
function insert_user($link, $username, $email, $password, $role, $tenant_id) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($link,
        "INSERT IGNORE INTO users (username, email, password, role, tenant_id) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssi', $username, $email, $hash, $role, $tenant_id);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);
    // If IGNORE triggered (duplicate), fetch the existing id
    if (!$id) {
        $r = mysqli_query($link, "SELECT id FROM users WHERE email='".mysqli_real_escape_string($link, $email)."'");
        $row = mysqli_fetch_assoc($r);
        $id = (int)$row['id'];
    }
    return $id;
}

function insert_tenant($link, $name, $owner_id, $plan) {
    $stmt = mysqli_prepare($link,
        "INSERT IGNORE INTO tenants (name, owner_id, plan, status) VALUES (?, ?, ?, 'active')");
    mysqli_stmt_bind_param($stmt, 'sis', $name, $owner_id, $plan);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);
    return $id;
}

function set_user_tenant($link, $user_id, $tenant_id) {
    $stmt = mysqli_prepare($link, "UPDATE users SET tenant_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $tenant_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ── Password for all sample accounts ────────────────────
$pw = 'Test@1234';
$log = [];

mysqli_begin_transaction($link);
try {
    // 1. Super Admin (uses tenant_id 0 — platform-level, no workspace)
    $sa_id = insert_user($link, 'Super Admin', 'superadmin@studyflow.com', $pw, 'super_admin', 0);
    $log[] = "✓ Super Admin  (superadmin@studyflow.com) — role: super_admin";

    // 2. Alex — Free plan
    $alex_id  = insert_user($link, 'Alex Free', 'alex@example.com', $pw, 'admin', 0);
    $alex_tid = insert_tenant($link, "Alex's Workspace", $alex_id, 'free');
    set_user_tenant($link, $alex_id, $alex_tid);
    $log[] = "✓ Alex Free    (alex@example.com)           — plan: free";

    // 3. Beth — Basic plan
    $beth_id  = insert_user($link, 'Beth Basic', 'beth@example.com', $pw, 'admin', 0);
    $beth_tid = insert_tenant($link, "Beth's Workspace", $beth_id, 'basic');
    set_user_tenant($link, $beth_id, $beth_tid);
    $log[] = "✓ Beth Basic   (beth@example.com)           — plan: basic";

    // 4. Carlos — Standard plan
    $carlos_id  = insert_user($link, 'Carlos Standard', 'carlos@example.com', $pw, 'admin', 0);
    $carlos_tid = insert_tenant($link, "Carlos's Workspace", $carlos_id, 'standard');
    set_user_tenant($link, $carlos_id, $carlos_tid);
    $log[] = "✓ Carlos Standard (carlos@example.com)      — plan: standard";

    // 5. Diana — Premium plan
    $diana_id  = insert_user($link, 'Diana Premium', 'diana@example.com', $pw, 'admin', 0);
    $diana_tid = insert_tenant($link, "Diana's Workspace", $diana_id, 'premium');
    set_user_tenant($link, $diana_id, $diana_tid);
    $log[] = "✓ Diana Premium (diana@example.com)         — plan: premium";

    // 6. Eve — user under Carlos's standard workspace
    $eve_id = insert_user($link, 'Eve User', 'eve@example.com', $pw, 'user', $carlos_tid);
    $log[] = "✓ Eve User     (eve@example.com)            — member of Carlos's workspace";

    // 7. Frank — user under Diana's premium workspace
    $frank_id = insert_user($link, 'Frank User', 'frank@example.com', $pw, 'user', $diana_tid);
    $log[] = "✓ Frank User   (frank@example.com)          — member of Diana's workspace";

    mysqli_commit($link);
    $status = 'success';
} catch (Exception $e) {
    mysqli_rollback($link);
    $status = 'error';
    $log[] = '✗ Error: ' . $e->getMessage();
}

// ── Output ───────────────────────────────────────────────
if (php_sapi_name() === 'cli') {
    echo implode("\n", $log) . "\n";
    echo ($status === 'success') ? "\nDone! Password for all accounts: Test@1234\n" : "\nFailed.\n";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Seeder Result</title>
<style>body{font-family:sans-serif;max-width:640px;margin:3rem auto;padding:0 1rem;}
pre{background:#f0fdf4;border:1px solid #bbf7d0;padding:1rem;border-radius:6px;line-height:1.7;}
.err pre{background:#fef2f2;border-color:#fca5a5;}
a{color:#7c3aed;font-weight:700;}
</style></head>
<body>
<h2><?php echo $status === 'success' ? '✅ Seeder Complete' : '❌ Seeder Failed'; ?></h2>
<pre><?php echo implode("\n", $log); ?></pre>
<?php if ($status === 'success'): ?>
<p><strong>Password for all accounts:</strong> <code>Test@1234</code></p>
<p style="color:#dc2626;font-weight:600;">⚠ Delete this file now: <code>seed_sample_data.php</code></p>
<p><a href="login.php">→ Go to Login</a></p>
<?php endif; ?>
</body></html>
<?php
if ($link) mysqli_close($link);
