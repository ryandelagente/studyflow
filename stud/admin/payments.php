<?php
// Initialize the session
session_start();
 
// Check if the user is logged in and is an admin/super_admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'admin' && $_SESSION["role"] !== 'super_admin')){
    header("location: ../login.php");
    exit;
}
 
require_once(__DIR__ . '/../config.php');

$payments = [];

if ($link) {
    // Fetch a list of tenants and their owners to simulate a payment list
    $sql = "SELECT t.name as workspace_name, u.username 
            FROM tenants t 
            JOIN users u ON t.owner_id = u.id 
            ORDER BY t.created_at DESC LIMIT 16"; // Limiting to 16 for pagination example
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <h2 class="text-2xl font-bold text-gray-800 mb-8">Payments / <span class="text-gray-500">Transactions</span></h2>
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
                        <th class="p-2 font-medium">WORKSPACE</th>
                        <th class="p-2 font-medium">USER</th>
                        <th class="p-2 font-medium">PLAN</th>
                        <th class="p-2 font-medium">AMOUNT</th>
                        <th class="p-2 font-medium">CREATED</th>
                        <th class="p-2 font-medium text-right">MANAGE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr class="border-t"><td colspan="6" class="text-center p-8 text-gray-500">No payments found.</td></tr>
                    <?php else: ?>
                        <?php foreach($payments as $payment): ?>
                        <tr class="border-t">
                            <td class="p-3 font-semibold text-gray-800"><?php echo htmlspecialchars($payment['workspace_name']); ?></td>
                            <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($payment['username']); ?></td>
                            <td class="p-3 text-sm text-gray-600">--</td>
                            <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($payment['username']); ?></td> <td class="p-3 text-sm text-gray-600">49 minutes ago</td> <td class="p-3 text-right">
                                <a href="#" class="inline-block p-2 rounded-full hover:bg-gray-100">
                                    <i data-lucide="more-horizontal" class="w-5 h-5 text-gray-500"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
         <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
            <div>Showing 1 to 10 of <?php echo count($payments); ?> entries</div>
            <div class="flex items-center">
                <a href="#" class="px-3 py-1 border rounded-l-md bg-gray-100 text-gray-400">Previous</a>
                <a href="#" class="px-3 py-1 border-t border-b bg-purple-600 text-white">1</a>
                <a href="#" class="px-3 py-1 border-t border-b border-r rounded-r-md hover:bg-gray-100">2</a>
            </div>
        </div>
    </div>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>