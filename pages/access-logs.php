<?php
// File: /pages/access-logs.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('location: ../index.php'); exit;
}

require_once(__DIR__ . '/../config.php');

$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
$logs = [];
$total = 0;

$per_page = 15;
$page     = max(1, (int)($_GET['p'] ?? 1));
$offset   = ($page - 1) * $per_page;
$search   = trim($_GET['q'] ?? '');

if ($link) {
    $where = "WHERE al.tenant_id = ?";
    $params = [$tenant_id];
    $types  = 'i';

    if ($search !== '') {
        $where .= " AND (u.username LIKE ? OR al.ip_address LIKE ? OR al.action LIKE ?)";
        $like = "%$search%";
        $params = array_merge($params, [$like, $like, $like]);
        $types .= 'sss';
    }

    // Total count
    $cnt_sql = "SELECT COUNT(*) FROM access_logs al JOIN users u ON al.user_id = u.id $where";
    $cnt_stmt = mysqli_prepare($link, $cnt_sql);
    mysqli_stmt_bind_param($cnt_stmt, $types, ...$params);
    mysqli_stmt_execute($cnt_stmt);
    mysqli_stmt_bind_result($cnt_stmt, $total);
    mysqli_stmt_fetch($cnt_stmt);
    mysqli_stmt_close($cnt_stmt);

    // Fetch page
    $sql = "SELECT al.id, u.username, al.ip_address, al.user_agent, al.action, al.created_at
            FROM access_logs al
            JOIN users u ON al.user_id = u.id
            $where
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) { $logs[] = $row; }
    mysqli_stmt_close($stmt);
}

$total_pages = max(1, (int)ceil($total / $per_page));

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold">Access Logs</h2>
        <p class="text-gray-500">Settings / Access Logs — login history for your workspace</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <!-- Search + count -->
        <form method="GET" class="flex justify-between items-center mb-4 gap-4">
            <p class="text-sm text-gray-500 shrink-0"><?php echo $total; ?> total entries</p>
            <div class="flex gap-2">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search user, IP, action..."
                       class="border rounded-md px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm hover:bg-purple-700">Search</button>
                <?php if ($search): ?>
                <a href="access-logs.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3 font-semibold">USER</th>
                    <th class="p-3 font-semibold">IP</th>
                    <th class="p-3 font-semibold">ACTION</th>
                    <th class="p-3 font-semibold">DEVICE</th>
                    <th class="p-3 font-semibold">ACCESSED ON</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="text-center p-16 text-gray-400">No log entries yet.</td></tr>
                <?php else: foreach ($logs as $log): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium"><?php echo htmlspecialchars($log['username']); ?></td>
                    <td class="p-3 font-mono text-gray-600"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </span>
                    </td>
                    <td class="p-3 text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                        <?php echo htmlspecialchars(substr($log['user_agent'], 0, 60)) . (strlen($log['user_agent']) > 60 ? '…' : ''); ?>
                    </td>
                    <td class="p-3 text-gray-500"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
            <div>Showing <?php echo $total > 0 ? $offset + 1 : 0; ?>–<?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?></div>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?p=<?php echo $i; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>"
                   class="px-3 py-1 border rounded <?php echo $i === $page ? 'bg-purple-600 text-white border-purple-600' : 'bg-gray-100 hover:bg-gray-200'; ?>">
                   <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>
