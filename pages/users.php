<?php
// File: /pages/users.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php');
    exit;
}
if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header('location: ../index.php');
    exit;
}
$is_super_admin = ($_SESSION['role'] === 'super_admin');

require_once(__DIR__ . '/../config.php');

$users = [];
$success_message = '';
$error_message = '';
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

// --- CRUD ---
if ($link && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { $error_message = "Security token mismatch. Please try again."; }
    else {
    // ADD USER
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role     = trim($_POST['role']);

        $check = mysqli_prepare($link, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $error_message = "A user with that email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($link, "INSERT INTO users (username, email, password, role, tenant_id) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssssi', $username, $email, $hashed, $role, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: users.php?success=1');
                exit;
            } else {
                $error_message = "Error adding user.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }

    // DELETE USER
    if (isset($_POST['delete_user'])) {
        $del_id = (int)$_POST['user_id'];
        if ($del_id === (int)$_SESSION['id']) {
            $error_message = "You cannot delete your own account.";
        } else {
            $stmt = mysqli_prepare($link, "DELETE FROM users WHERE id = ? AND tenant_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $del_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: users.php?success=2');
                exit;
            }
            mysqli_stmt_close($stmt);
        }
    }
    } // end csrf else
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) $success_message = "User added successfully.";
    if ($_GET['success'] == 2) $success_message = "User deleted.";
}

// --- READ ---
// Super admin sees ALL users across all tenants; admin sees only own tenant
if ($link) {
    if ($is_super_admin) {
        $stmt = mysqli_prepare($link,
            "SELECT u.id, u.username, u.email, u.role, u.created_at, t.name AS tenant_name, t.plan AS tenant_plan
             FROM users u
             LEFT JOIN tenants t ON t.id = u.tenant_id
             ORDER BY u.created_at DESC");
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($link,
            "SELECT u.id, u.username, u.email, u.role, u.created_at,
                    NULL AS tenant_name, NULL AS tenant_plan
             FROM users u
             WHERE u.tenant_id = ?
             ORDER BY u.created_at DESC");
        mysqli_stmt_bind_param($stmt, 'i', $tenant_id);
        mysqli_stmt_execute($stmt);
    }
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    mysqli_stmt_close($stmt);
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">Users</h2>
            <p class="text-gray-500">Settings / Users<?php echo $is_super_admin ? ' <span class="text-purple-600 font-semibold">— All tenants</span>' : ''; ?></p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">
            Add User
        </button>
    </div>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="p-3">NAME</th>
                    <th class="p-3">EMAIL</th>
                    <th class="p-3">ROLE</th>
                    <?php if ($is_super_admin): ?>
                    <th class="p-3">WORKSPACE / PLAN</th>
                    <?php endif; ?>
                    <th class="p-3">CREATED</th>
                    <th class="p-3 text-right">MANAGE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center p-8 text-gray-500">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-semibold flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                <?php
                                    $parts = explode(' ', trim($u['username']));
                                    echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                ?>
                            </div>
                            <?php echo htmlspecialchars($u['username']); ?>
                        </td>
                        <td class="p-3 text-gray-600"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td class="p-3">
                            <?php
                                $role_class = match($u['role']) {
                                    'super_admin' => 'bg-red-100 text-red-800',
                                    'admin'       => 'bg-blue-100 text-blue-800',
                                    default       => 'bg-gray-100 text-gray-800',
                                };
                            ?>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?php echo $role_class; ?>">
                                <?php echo htmlspecialchars($u['role']); ?>
                            </span>
                        </td>
                        <?php if ($is_super_admin): ?>
                        <td class="p-3 text-sm text-gray-600">
                            <?php if (!empty($u['tenant_name'])): ?>
                              <span class="font-medium"><?php echo htmlspecialchars($u['tenant_name']); ?></span>
                              <?php
                                $plan_class = match($u['tenant_plan'] ?? '') {
                                    'premium'  => 'bg-yellow-100 text-yellow-800',
                                    'standard' => 'bg-purple-100 text-purple-800',
                                    'basic'    => 'bg-blue-100 text-blue-800',
                                    'free'     => 'bg-gray-100 text-gray-600',
                                    default    => 'bg-gray-100 text-gray-400',
                                };
                              ?>
                              <span class="ml-1 text-xs font-bold px-1.5 py-0.5 rounded <?php echo $plan_class; ?>">
                                <?php echo strtoupper($u['tenant_plan'] ?: 'free'); ?>
                              </span>
                            <?php else: ?>
                              <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td class="p-3 text-gray-500 text-sm"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td class="p-3 text-right">
                            <?php if ($u['id'] !== (int)$_SESSION['id']): ?>
                            <form method="POST" onsubmit="return confirm('Delete this user?');" class="inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="delete_user" class="text-red-500 hover:text-red-700 text-sm font-medium">Delete</button>
                            </form>
                            <?php else: ?>
                            <span class="text-gray-400 text-sm">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <h2 class="text-xl font-bold mb-6">Add New User</h2>
        <form method="POST"><?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="username" class="w-full border rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" class="w-full border rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full border rounded-md p-2" minlength="6" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" class="w-full border rounded-md p-2">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <?php if ($is_super_admin): ?>
                    <option value="super_admin">Super Admin</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="add_user" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Add User</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) mysqli_close($link);
?>
