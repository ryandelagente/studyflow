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
$workspace_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$workspaces = [];
$workspace_details = null;
$workspace_users = [];

if ($link) {
    if ($action === 'list') {
        // Fetch all workspaces and their associated users
        $sql = "SELECT t.id, t.name, t.status, t.created_at, u.username as owner_name, 
                       (SELECT GROUP_CONCAT(CONCAT(username, ':', email) SEPARATOR ';') FROM users WHERE tenant_id = t.id) as users_data
                FROM tenants t 
                JOIN users u ON t.owner_id = u.id 
                ORDER BY t.created_at DESC";
        $result = mysqli_query($link, $sql);
        while($row = mysqli_fetch_assoc($result)) {
            $workspaces[] = $row;
        }
    } elseif ($action === 'view' && $workspace_id > 0) {
        // Fetch details for a single workspace
        $sql_details = "SELECT name, status FROM tenants WHERE id = ?";
        if($stmt = mysqli_prepare($link, $sql_details)){
            mysqli_stmt_bind_param($stmt, "i", $workspace_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $workspace_details = mysqli_fetch_assoc($result);
        }

        // Fetch users for that workspace
        $sql_users = "SELECT username, email FROM users WHERE tenant_id = ?";
        if($stmt = mysqli_prepare($link, $sql_users)){
            mysqli_stmt_bind_param($stmt, "i", $workspace_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while($row = mysqli_fetch_assoc($result)){
                $workspace_users[] = $row;
            }
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <?php if ($action === 'list'): ?>
        <h2 class="text-2xl font-bold text-gray-800 mb-8">Workspaces</h2>
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
                            <th class="p-2 font-medium">USERS</th>
                            <th class="p-2 font-medium">STATUS</th>
                            <th class="p-2 font-medium">CREATED</th>
                            <th class="p-2 font-medium text-right">MANAGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($workspaces as $ws): ?>
                        <tr class="border-t">
                            <td class="p-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm mr-3 shrink-0">
                                        <?php echo htmlspecialchars(strtoupper(substr($ws['name'], 0, 1))); ?>
                                    </div>
                                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($ws['name']); ?></span>
                                </div>
                            </td>
                            <td class="p-3 text-sm text-blue-600">
                                <?php if (!empty($ws['users_data'])): ?>
                                    <?php $users = explode(';', $ws['users_data']); ?>
                                    <?php foreach ($users as $user_str): ?>
                                        <?php list($name, $email) = explode(':', $user_str, 2); ?>
                                        <div class="truncate"><?php echo htmlspecialchars($name); ?> (<?php echo htmlspecialchars($email); ?>)</div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 uppercase"><?php echo htmlspecialchars($ws['status']); ?></span>
                            </td>
                            <td class="p-3 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($ws['created_at'])); ?></td>
                            <td class="p-3 text-right">
                                <a href="workspaces.php?action=view&id=<?php echo $ws['id']; ?>" class="inline-block p-2 rounded-full hover:bg-gray-100">
                                    <i data-lucide="more-horizontal" class="w-5 h-5 text-gray-500"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($action === 'view' && $workspace_details): ?>
        <h2 class="text-2xl font-bold text-gray-800 mb-8">
            <a href="workspaces.php" class="text-gray-400 hover:text-purple-600">Workspaces</a> / <?php echo htmlspecialchars($workspace_details['name']); ?>
        </h2>
        <div class="bg-white p-8 rounded-lg shadow-sm max-w-2xl">
            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($workspace_details['name']); ?></h3>
            <p class="text-gray-500 mt-1">Total Users: <?php echo count($workspace_users); ?></p>
            
            <div class="mt-6">
                <h4 class="font-semibold text-lg text-gray-700">Users</h4>
                <div class="border rounded-lg mt-2">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-500 text-sm bg-gray-50">
                                <th class="p-2 font-medium">NAME</th>
                                <th class="p-2 font-medium">EMAIL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($workspace_users as $user): ?>
                                <tr class="border-t">
                                    <td class="p-2"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="font-semibold text-lg text-gray-700">Storage Space Used</h4>
                <p class="text-3xl font-bold text-gray-800 mt-1">0mb</p>
            </div>

            <div class="mt-8">
                <h4 class="font-semibold text-lg text-gray-700">Actions</h4>
                <button class="mt-2 bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold hover:bg-red-200">Deactivate Workspace</button>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-red-600">Workspace Not Found</h2>
            <p class="mt-2 text-gray-600">The requested workspace could not be found. <a href="workspaces.php" class="text-purple-600 hover:underline">Return to list</a>.</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>