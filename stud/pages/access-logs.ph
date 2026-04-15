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
            <a href="#" class="text-gray-400 hover:text-purple-600">Quick Share</a> / Access logs
        </h2>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-2xl font-bold mb-4">Access Logs</h3>
        <div class="flex justify-between items-center mb-4">
            
<div class="flex items-center text-sm text-gray-600">
                <span>Show</span>
                <select class="mx-2 border rounded-md p-1.5"><option>10</option></select>
                <span>entries</span>
            </div>
            <div class="w-1/3"><input type="text" placeholder="Search..." class="border rounded-md p-2 w-full"></div>
        </div>
     
   <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="p-2 font-semibold">IP</th>
                    <th class="p-2 font-semibold">DEVICE</th>
                    
<th class="p-2 font-semibold">ACCESSED ON</th>
                    <th class="p-2 font-semibold text-right">MANAGE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                  
   <td colspan="4" class="text-center p-16 text-gray-500">No data available in table</td>
                </tr>
            </tbody>
        </table>
        <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
            <div>Showing 0 to 0 of 0 entries</div>
            <div class="flex items-center">
        
        <a href="#" class="px-3 py-1 border rounded-l-md bg-gray-100 text-gray-400">Previous</a>
                <a href="#" class="px-3 py-1 border rounded-r-md bg-gray-100 text-gray-400">Next</a>
            </div>
        </div>
    </div>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
?>