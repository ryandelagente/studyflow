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
$file_path_info = isset($_GET['file']) ? explode('/', base64_decode($_GET['file'])) : null;

$all_files = [];
$view_file = null;

if ($link) {
    if ($action === 'list') {
        // Fetch all tenants and their owners
        $tenants = [];
        $sql_tenants = "SELECT t.id, t.name as workspace_name, u.username as owner_name FROM tenants t JOIN users u ON t.owner_id = u.id";
        $result_tenants = mysqli_query($link, $sql_tenants);
        while($row = mysqli_fetch_assoc($result_tenants)){
            $tenants[$row['id']] = $row;
        }

        // Scan all tenant upload directories for files
        $base_upload_dir = BASE_PATH . '/uploads/';
        foreach ($tenants as $tenant_id => $tenant_data) {
            $tenant_dir = $base_upload_dir . $tenant_id . '/';
            if (is_dir($tenant_dir)) {
                $files = array_diff(scandir($tenant_dir), ['.', '..']);
                foreach ($files as $file) {
                    $all_files[] = [
                        'name' => $file,
                        'workspace_name' => $tenant_data['workspace_name'],
                        'owner_name' => $tenant_data['owner_name'],
                        'created' => filemtime($tenant_dir . $file),
                        'tenant_id' => $tenant_id
                    ];
                }
            }
        }
    } elseif ($action === 'view' && count($file_path_info) === 2) {
        list($tenant_id, $file_name) = $file_path_info;
        $file_path = BASE_PATH . '/uploads/' . $tenant_id . '/' . $file_name;
        if(file_exists($file_path)){
             $view_file = [
                'name' => $file_name,
                'url' => BASE_URL . '/uploads/' . $tenant_id . '/' . $file_name,
            ];
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <?php if ($action === 'list'): ?>
        <h2 class="text-2xl font-bold text-gray-800 mb-8">Files</h2>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                 <h3 class="text-xl font-semibold">Digital Assets</h3>
                <button class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">Upload</button>
            </div>
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
                            <th class="p-2 font-medium">TITLE</th>
                            <th class="p-2 font-medium">WORKSPACE</th>
                            <th class="p-2 font-medium">OWNER</th>
                            <th class="p-2 font-medium">CREATED</th>
                            <th class="p-2 font-medium text-right">MANAGE</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if (empty($all_files)): ?>
                            <tr class="border-t"><td colspan="5" class="text-center p-8 text-gray-500">No files found.</td></tr>
                        <?php else: ?>
                            <?php foreach($all_files as $file): ?>
                            <tr class="border-t">
                                <td class="p-3">
                                    <a href="files.php?action=view&file=<?php echo base64_encode($file['tenant_id'] . '/' . $file['name']); ?>" class="font-semibold text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($file['name']); ?>
                                    </a>
                                </td>
                                <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($file['workspace_name']); ?></td>
                                <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($file['owner_name']); ?></td>
                                <td class="p-3 text-sm text-gray-600"><?php echo date('M d, Y', $file['created']); ?></td>
                                <td class="p-3 text-right">
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
                <div>Showing 1 to <?php echo count($all_files); ?> of <?php echo count($all_files); ?> entries</div>
                <div class="flex items-center">
                    <a href="#" class="px-3 py-1 border rounded-l-md bg-gray-100 text-gray-400">Previous</a>
                    <a href="#" class="px-3 py-1 border-t border-b border-r rounded-r-md bg-purple-600 text-white">1</a>
                </div>
            </div>
        </div>
    <?php elseif ($action === 'view' && $view_file): ?>
        <h2 class="text-2xl font-bold text-gray-800 mb-8"><?php echo htmlspecialchars($view_file['name']); ?></h2>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($view_file['name']); ?></h3>
            <div class="flex items-center space-x-2">
                 <a href="files.php" class="bg-yellow-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-yellow-600">Back</a>
                 <a href="<?php echo $view_file['url']; ?>" download class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">Download</a>
                 <button type="button" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">Share</button>
            </div>
        </div>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200">
            <img src="<?php echo htmlspecialchars($view_file['url']); ?>" alt="<?php echo htmlspecialchars($view_file['name']); ?>" class="max-w-full h-auto mx-auto rounded">
        </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-red-600">File Not Found</h2>
            <p class="mt-2 text-gray-600">The requested file could not be found. <a href="files.php" class="text-purple-600 hover:underline">Return to list</a>.</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>