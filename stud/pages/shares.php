<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once(__DIR__ . '/../config.php');
require_once(BASE_PATH . '/partials/header.php');

// SAAS Fix: You can use the tenant_id for future queries
$tenant_id = $_SESSION["tenant_id"];
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">
            <a href="#" class="text-gray-400 hover:text-purple-600">Quick Share</a> / Manage your shares
        </h2>
    </div>
    <div class="bg-white p-12 rounded-lg shadow-md w-full text-center">
        <div class="flex justify-center mb-4">
            <i data-lucide="file-question" class="w-16 h-16 text-blue-400"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800">No Files Found</h3>
        <p class="text-gray-500 mt-2">You have not shared any files yet.</p>
    </div>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
?>