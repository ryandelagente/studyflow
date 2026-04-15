<?php
// Initialize the session
session_start();
 
// Check if the user is logged in and is an admin/super_admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'admin' && $_SESSION["role"] !== 'super_admin')){
    header("location: ../login.php");
    exit;
}
 
require_once(__DIR__ . '/../config.php');

// --- PAGE ROUTING ---
$action = $_GET['action'] ?? 'list';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$all_users = [];
$user_details = null;

if ($link) {
    if ($action === 'list') {
        // Fetch all users across all tenants
        $sql = "SELECT u.id, u.username, u.email, u.created_at, c.phone 
                FROM users u 
                LEFT JOIN contacts c ON u.email = c.email AND u.tenant_id = c.tenant_id
                ORDER BY u.created_at DESC";
        $result = mysqli_query($link, $sql);
        while($row = mysqli_fetch_assoc($result)) {
            $all_users[] = $row;
        }
    } elseif ($action === 'view' && $user_id > 0) {
        // Fetch details for a single user, including their workspace name
        $sql = "SELECT u.username, u.email, u.created_at, t.name as workspace_name, c.phone
                FROM users u 
                LEFT JOIN tenants t ON u.tenant_id = t.id
                LEFT JOIN contacts c ON u.email = c.email AND u.tenant_id = c.tenant_id
                WHERE u.id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user_details = mysqli_fetch_assoc($result);
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <?php if ($action === 'list'): ?>
        <h2 class="text-2xl font-bold text-gray-800 mb-8">Users</h2>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center text-sm text-gray-600">
                    <span>Show</span>
                    <select class="mx-2 border rounded-md p-1.5"><option>10</option></select>
                    <span>entries</span>
                </div>
                <div class="w-1/3"><input type="text" placeholder="Search..." class="border rounded-md p-2 w-full"></div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="p-2 font-medium">NAME</th>
                            <th class="p-2 font-medium">EMAIL</th>
                            <th class="p-2 font-medium">PHONE</th>
                            <th class="p-2 font-medium">CREATED</th>
                            <th class="p-2 font-medium text-right">MANAGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($all_users as $user): ?>
                        <tr class="border-t">
                            <td class="p-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm mr-3 shrink-0">
                                        <?php echo htmlspecialchars(strtoupper(substr($user['username'], 0, 1))); ?>
                                    </div>
                                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                            </td>
                            <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($user['phone'] ?? '--'); ?></td>
                            <td class="p-3 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="p-3 text-right">
                                <a href="users.php?action=view&id=<?php echo $user['id']; ?>" class="inline-block p-2 rounded-full hover:bg-gray-100">
                                    <i data-lucide="more-horizontal" class="w-5 h-5 text-gray-500"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                <div>Showing 1 to <?php echo min(10, count($all_users)); ?> of <?php echo count($all_users); ?> entries</div>
                <div class="flex items-center">
                    <a href="#" class="px-3 py-1 border rounded-l-md bg-gray-100 text-gray-400">Previous</a>
                    <a href="#" class="px-3 py-1 border-t border-b bg-purple-600 text-white">1</a>
                    <a href="#" class="px-3 py-1 border-t border-b hover:bg-gray-100">2</a>
                    <a href="#" class="px-3 py-1 border-t border-b hover:bg-gray-100">3</a>
                    <a href="#" class="px-3 py-1 border-t border-b hover:bg-gray-100">4</a>
                    <a href="#" class="px-3 py-1 border-t border-b hover:bg-gray-100">5</a>
                    <a href="#" class="px-3 py-1 border-t border-b border-r rounded-r-md hover:bg-gray-100">Next</a>
                </div>
            </div>
        </div>

    <?php elseif ($action === 'view' && $user_details): ?>
        <h2 class="text-2xl font-bold text-gray-800 mb-8">
            <a href="users.php" class="text-gray-400 hover:text-purple-600">Users</a> / <?php echo htmlspecialchars($user_details['username']); ?>
        </h2>
        <div class="bg-white p-8 rounded-lg shadow-sm max-w-lg">
            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($user_details['username']); ?></h3>
            <div class="mt-4 space-y-2 text-gray-600">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_details['phone'] ?? 'N/A'); ?></p>
                <p><strong>Workspace:</strong> <?php echo htmlspecialchars($user_details['workspace_name']); ?></p>
                <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($user_details['created_at'])); ?></p>
                <p><strong>Last Login:</strong> 2 days ago</p> </div>
        </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-red-600">User Not Found</h2>
            <p class="mt-2 text-gray-600">The requested user could not be found. <a href="users.php" class="text-purple-600 hover:underline">Return to list</a>.</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>