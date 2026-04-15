<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Check if the user is an admin or super_admin, otherwise redirect to user dashboard
if($_SESSION["role"] !== 'admin' && $_SESSION["role"] !== 'super_admin'){
    header("location: ../index.php");
    exit;
}
 
// --- SETUP ---
require_once(__DIR__ . '/../config.php');

// --- DATA FETCHING for Admin Dashboard ---
$workspace_count = 0;
$user_count = 0;
$recent_users = [];
$recent_workspaces = [];

if ($link) {
    // Get total workspace (tenant) count
    $result = mysqli_query($link, "SELECT COUNT(id) as count FROM tenants");
    $workspace_count = mysqli_fetch_assoc($result)['count'];

    // Get total user count
    $result = mysqli_query($link, "SELECT COUNT(id) as count FROM users");
    $user_count = mysqli_fetch_assoc($result)['count'];

    // Get recent users
    $sql_recent_users = "SELECT u.username, t.name as workspace_name, u.email, u.created_at 
                         FROM users u JOIN tenants t ON u.tenant_id = t.id 
                         ORDER BY u.created_at DESC LIMIT 5";
    $result = mysqli_query($link, $sql_recent_users);
    while($row = mysqli_fetch_assoc($result)){
        $recent_users[] = $row;
    }

    // Get recent workspaces
    $sql_recent_workspaces = "SELECT t.name, u.username as owner_name, t.status, t.created_at 
                              FROM tenants t JOIN users u ON t.owner_id = u.id 
                              ORDER BY t.created_at DESC LIMIT 5";
    $result = mysqli_query($link, $sql_recent_workspaces);
    while($row = mysqli_fetch_assoc($result)){
        $recent_workspaces[] = $row;
    }
}


require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <h2 class="text-2xl font-bold text-gray-800 mb-8">Dashboard</h2>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-500">Workspaces</h3>
            <p class="text-4xl font-bold mt-2 text-gray-800"><?php echo $workspace_count; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-500">Users</h3>
            <p class="text-4xl font-bold mt-2 text-gray-800"><?php echo $user_count; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-500">Storage Space</h3>
            <p class="text-4xl font-bold mt-2 text-gray-800">0.37<span class="text-2xl text-gray-400">mb</span></p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="font-semibold text-gray-700">Transactions</h3>
            <canvas id="transactionsChart" class="mt-4"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="font-semibold text-gray-700">User Acquisitions</h3>
            <canvas id="acquisitionsChart" class="mt-4"></canvas>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
        <h3 class="font-semibold text-gray-700 mb-4">Recent Users</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm">
                        <th class="p-2 font-medium">NAME</th>
                        <th class="p-2 font-medium">WORKSPACE</th>
                        <th class="p-2 font-medium">EMAIL</th>
                        <th class="p-2 font-medium">CREATED</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_users as $user): ?>
                    <tr class="border-t">
                        <td class="p-3 font-semibold"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="p-3 text-purple-600 font-semibold"><?php echo htmlspecialchars($user['workspace_name']); ?></td>
                        <td class="p-3"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="p-3"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Workspaces Table -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="font-semibold text-gray-700 mb-4">Recent Workspaces</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm">
                        <th class="p-2 font-medium">NAME</th>
                        <th class="p-2 font-medium">USER</th>
                        <th class="p-2 font-medium">STATUS</th>
                        <th class="p-2 font-medium">CREATED</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_workspaces as $workspace): ?>
                    <tr class="border-t">
                        <td class="p-3 font-semibold"><?php echo htmlspecialchars($workspace['name']); ?></td>
                        <td class="p-3 text-purple-600 font-semibold"><?php echo htmlspecialchars($workspace['owner_name']); ?></td>
                        <td class="p-3"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 uppercase"><?php echo htmlspecialchars($workspace['status']); ?></span></td>
                        <td class="p-3"><?php echo date('M d, Y', strtotime($workspace['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Transactions Chart
    const txCtx = document.getElementById('transactionsChart').getContext('2d');
    new Chart(txCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Transactions',
                data: [1200, 1900, 300, 500, 200, 300, 1500, 1300, 800, 1100, 900, 1700],
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderColor: 'rgba(139, 92, 246, 1)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });

    // User Acquisitions Chart
    const acqCtx = document.getElementById('acquisitionsChart').getContext('2d');
    new Chart(acqCtx, {
        type: 'line',
        data: {
            labels: ['2025-09-17', '2025-09-23', '2025-09-27', '2025-10-01', '2025-10-05', '2025-10-09'],
            datasets: [{
                label: 'New Users',
                data: [65, 59, 10, 5, 2, 1],
                fill: true,
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderColor: 'rgba(139, 92, 246, 1)',
                tension: 0.3
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
});
</script>

<?php
// Include the footer
require_once(BASE_PATH . '/partials/footer.php');
?>