<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/api-settings.php

require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session
$action = $_GET['action'] ?? 'list'; // 'list' or 'create'
$api_key_generated = null;

// --- PAGE LOGIC ---
if ($action === 'create') {
    // Generate a new random API key for display
    // In a real application, you would save this to the database against the tenant_id.
    $api_key_generated = bin2hex(random_bytes(24));
}

// --- RENDER PAGE ---
require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <?php if ($action === 'create'): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold">
                <a href="api-settings.php" class="text-gray-400 hover:text-purple-600">API Keys</a> / Edit API Key
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-6">API</h3>
                <form method="POST"> <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" value="New API Key" class="mt-1 block w-full border rounded-md p-2 bg-gray-50">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">API Key</label>
                        <input type="text" readonly value="<?php echo htmlspecialchars($api_key_generated); ?>" class="mt-1 block w-full border rounded-md p-2 bg-gray-100 text-gray-500">
                    </div>
                    <button type="submit" name="save_api_key" class="bg-purple-600 text-white px-8 py-2 rounded-lg font-semibold hover:bg-purple-700">Save</button>
                </form>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-6">Sample API Request</h3>
                <div class="space-y-4">
                    <p class="text-sm">
                        <span class="font-semibold">POST Request:</span>
                        <code class="block bg-gray-100 p-2 rounded text-xs mt-1">https://studybuddy.cloudonex.com/api/contact</code>
                    </p>
                    <div>
                        <p class="font-semibold text-sm mb-2">Parameters:</p>
                        <ul class="list-disc list-inside text-sm text-pink-600 space-y-1">
                            <li>api_key - <span class="text-gray-700"><?php echo htmlspecialchars($api_key_generated); ?></span></li>
                            <li>first_name - <span class="text-gray-700">First Name</span></li>
                            <li>last_name - <span class="text-gray-700">Last Name</span></li>
                            <li>email - <span class="text-gray-700">Email</span></li>
                            <li>phone - <span class="text-gray-700">Phone</span></li>
                            <li>address - <span class="text-gray-700">Address</span></li>
                            <li>notes - <span class="text-gray-700">Notes</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold">Settings / <span class="text-gray-500">API settings</span></h2>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Users</h3>
                <a href="api-settings.php?action=create" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">Create API Key</a>
            </div>
            
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center text-sm text-gray-600">
                    <span>Show</span>
                    <select class="mx-2 border rounded-md p-1.5 focus:ring-purple-500 focus:border-purple-500">
                        <option>10</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="w-1/3">
                    <input type="text" placeholder="Search..." class="border rounded-md p-2 w-full focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

             <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm border-b">
                        <th class="p-2 font-semibold">LABEL</th>
                        <th class="p-2 font-semibold">OWNER</th>
                        <th class="p-2 font-semibold">CREATED</th>
                        <th class="p-2 font-semibold text-right">MANAGE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td colspan="4" class="text-center p-8 text-gray-500">No data available in table</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                <div>Showing 0 to 0 of 0 entries</div>
                <div class="flex items-center">
                    <a href="#" class="px-3 py-1 border rounded-l-md bg-gray-100 text-gray-400 cursor-not-allowed">Previous</a>
                    <a href="#" class="px-3 py-1 border-t border-b border-r rounded-r-md bg-gray-100 text-gray-400 cursor-not-allowed">Next</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
?>