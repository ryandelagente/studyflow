<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/users.php

require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session
$users = [];

// --- DATABASE CONNECTION CHECK ---
if (!$link) {
    // In a real app, you'd have more robust error handling
    die("Database connection failed.");
}

// --- READ DATA: Fetch all users for the current tenant ---
$sql = "SELECT id, username, email, role, created_at FROM users WHERE tenant_id = ? ORDER BY created_at DESC";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $tenant_id);
    mysqli_stmt_execute($stmt);
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
            <p class="text-gray-500">Settings / Users</p>
        </div>
        <button class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">Add User</button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="p-2">NAME</th>
                    <th class="p-2">EMAIL</th>
                    <th class="p-2">ROLE</th>
                    <th class="p-2">CREATED</th>
                    <th class="p-2 text-right">MANAGE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr class="border-t">
                        <td colspan="5" class="text-center p-8 text-gray-500">No users found in this workspace.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-t">
                            <td class="p-2 font-semibold"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="p-2"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td class="p-2"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="p-2 text-right">...</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
?>