<?php
// File: /pages/directory.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

// Fetch all members of the same tenant (excluding self)
$members = [];
if ($link) {
    $stmt = mysqli_prepare($link, "SELECT id, username, email, role, created_at FROM users WHERE tenant_id = ? ORDER BY username ASC");
    mysqli_stmt_bind_param($stmt, 'i', $tenant_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) { $members[] = $row; }
    mysqli_stmt_close($stmt);
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">Directory</h2>
            <p class="text-gray-500">Members in your workspace</p>
        </div>
    </div>

    <!-- Search bar -->
    <div class="mb-6">
        <input type="text" id="dirSearch" placeholder="Search members..."
               class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-left font-semibold text-gray-600">Member</th>
                    <th class="p-4 text-left font-semibold text-gray-600">Email</th>
                    <th class="p-4 text-left font-semibold text-gray-600">Role</th>
                    <th class="p-4 text-left font-semibold text-gray-600">Joined</th>
                </tr>
            </thead>
            <tbody id="dirBody">
                <?php if (empty($members)): ?>
                <tr><td colspan="4" class="p-8 text-center text-gray-400">No members found.</td></tr>
                <?php else: foreach ($members as $m):
                    $parts = explode(' ', trim($m['username']));
                    $initials = count($parts) >= 2
                        ? strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1))
                        : strtoupper(substr($parts[0],0,2));
                    $is_me = ($m['id'] == $user_id);
                ?>
                <tr class="border-b hover:bg-gray-50 dir-row">
                    <td class="p-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <span class="font-medium"><?php echo htmlspecialchars($m['username']); ?>
                            <?php if ($is_me): ?><span class="ml-1 text-xs text-gray-400">(you)</span><?php endif; ?>
                        </span>
                    </td>
                    <td class="p-4 text-gray-600"><?php echo htmlspecialchars($m['email']); ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            <?php echo $m['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'; ?>">
                            <?php echo ucfirst(htmlspecialchars($m['role'])); ?>
                        </span>
                    </td>
                    <td class="p-4 text-gray-500"><?php echo date('M d, Y', strtotime($m['created_at'])); ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.getElementById('dirSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.dir-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>
