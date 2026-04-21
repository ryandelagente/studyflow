<?php
// File: /pages/resources.php
// Purpose: A full-featured page to manage, upload, view, and delete user files.

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$error_message = '';
$success_message = '';
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

// Define the upload directory path
$upload_dir = BASE_PATH . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// --- VIEW ROUTING ---
$action = $_GET['action'] ?? 'list'; // 'list' or 'view'
$view_file = null;

if ($action === 'view' && isset($_GET['file'])) {
    // Sanitize filename to prevent directory traversal
    $file_name = basename($_GET['file']);
    $file_path = $upload_dir . $file_name;
    if (file_exists($file_path)) {
        $view_file = [
            'name' => $file_name,
            'url' => BASE_URL . '/uploads/' . $file_name,
            'extension' => strtolower(pathinfo($file_name, PATHINFO_EXTENSION))
        ];
    } else {
        // If file not found, redirect back to the list
        header("Location: resources.php");
        exit();
    }
}


// --- POST LOGIC (Upload/Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify()) { $error_message = "Security token mismatch."; goto render; }
    // HANDLE FILE UPLOAD
    if (isset($_POST['upload_file'])) {
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
            $safe_file_name = preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES['resource_file']['name']));
            $target_path = $upload_dir . $safe_file_name;
            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $target_path)) {
                header("Location: resources.php?success=1");
                exit();
            } else { $error_message = "Error moving the uploaded file."; }
        } else { $error_message = "No file was uploaded or an error occurred."; }
    }
    // HANDLE FILE DELETION
    if (isset($_POST['delete_file'])) {
        $safe_file_to_delete = basename(trim($_POST['file_name']));
        $file_path = $upload_dir . $safe_file_to_delete;
        if (file_exists($file_path) && unlink($file_path)) {
            header("Location: resources.php?success=2");
            exit();
        } else { $error_message = "File not found or could not be deleted."; }
    }
}

if (isset($_GET['success'])) {
    $success_message = ($_GET['success'] == 1) ? "File uploaded successfully." : "File deleted.";
}
$success_message = $success_message ?: flash_get('success');
$error_message   = $error_message   ?: flash_get('error');

// --- READ: Scan the directory to get the list of files for the list view ---
$resources = [];
if ($action === 'list') {
    $files = array_diff(scandir($upload_dir), ['.', '..']);
    foreach ($files as $file) {
        $resources[] = [
            'name' => $file,
            'owner' => 'Breanna Schultz', // Placeholder owner
            'created' => filemtime($upload_dir . $file)
        ];
    }
}

render:
require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">

    <?php if ($action === 'view' && isset($view_file)): ?>
    <div class="mb-8">
        <h2 class="text-3xl font-bold"><?php echo htmlspecialchars($view_file['name']); ?></h2>
    </div>
     <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($view_file['name']); ?></h2>
        </div>
        <div class="flex items-center space-x-2">
             <a href="resources.php" class="bg-yellow-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-yellow-600">Back</a>
             <a href="<?php echo htmlspecialchars($view_file['url']); ?>" download class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">Download</a>
             <button type="button" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">Share</button>
        </div>
    </div>
    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200">
        <?php if (in_array($view_file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
            <img src="<?php echo htmlspecialchars($view_file['url']); ?>" alt="<?php echo htmlspecialchars($view_file['name']); ?>" class="max-w-full h-auto mx-auto rounded">
        <?php else: ?>
            <div class="text-center py-20">
                <p class="text-gray-500">No preview available for this file type.</p>
                <p class="text-lg font-semibold mt-2"><?php echo htmlspecialchars($view_file['name']); ?></p>
            </div>
        <?php endif; ?>
    </div>


    <?php else: ?>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold">Resource Management</h2>
        </div>
        <button onclick="openModal('uploadModal')" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">
            Upload
        </button>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
         <div class="flex justify-between items-center mb-4">
            <div class="flex items-center text-sm text-gray-600">
                <span>Show</span>
                <select class="mx-2 border rounded-md p-1.5 focus:ring-purple-500 focus:border-purple-500"><option>10</option></select>
                <span>entries</span>
            </div>
            <div class="w-1/3"><input type="text" placeholder="Search..." class="border rounded-md p-2 w-full focus:ring-purple-500 focus:border-purple-500"></div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="p-2 font-semibold">TITLE</th>
                    <th class="p-2 font-semibold">OWNER</th>
                    <th class="p-2 font-semibold">CREATED</th>
                    <th class="p-2 font-semibold text-right">MANAGE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resources)): ?>
                    <tr><td colspan="4" class="text-center p-8 text-gray-500">No resources found.</td></tr>
                <?php else: ?>
                    <?php foreach ($resources as $resource): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3 font-medium">
                                <a href="resources.php?action=view&file=<?php echo urlencode($resource['name']); ?>" class="text-purple-600 hover:underline">
                                    <?php echo htmlspecialchars($resource['name']); ?>
                                </a>
                            </td>
                            <td class="p-3 text-gray-600"><?php echo htmlspecialchars($_SESSION['username'] ?? $resource['owner']); ?></td>
                            <td class="p-3 text-gray-600"><?php echo date('M d, Y', $resource['created']); ?></td>
                            <td class="p-3 text-right">
                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($resource['name']); ?>">
                                    <button type="submit" name="delete_file" class="text-gray-400 hover:text-red-500 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
         <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
            <div>Showing 1 to <?php echo count($resources); ?> of <?php echo count($resources); ?> entries</div>
            <div class="flex items-center">
                <a href="#" class="px-3 py-1 border rounded-l-md hover:bg-gray-100">Previous</a>
                <a href="#" class="px-3 py-1 border bg-purple-600 text-white hover:bg-purple-700">1</a>
                <a href="#" class="px-3 py-1 border rounded-r-md hover:bg-gray-100">Next</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Upload New Resource</h2>
        <form method="POST" action="resources.php" enctype="multipart/form-data"><?php echo csrf_field(); ?>
            <div class="mb-6">
                <input type="file" name="resource_file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('uploadModal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="upload_file" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>