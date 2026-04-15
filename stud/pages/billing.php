<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/billing.php

require_once(__DIR__ . '/../config.php');
require_once(BASE_PATH . '/partials/header.php');

// SAAS Fix: Use tenant_id to fetch subscription/plan details in the future
$tenant_id = $_SESSION["tenant_id"];
?>

<main class="flex-1 p-8 overflow-y-auto flex items-center justify-center">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-5xl">
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-lg font-semibold text-gray-500">BASIC</h3>
            <p class="text-4xl font-bold my-4">$4.99 <span class="text-lg font-normal text-gray-500">/month</span></p>
            <p class="text-sm text-gray-500 mb-6">$49.99 /year</p>
            <button class="w-full bg-purple-200 text-purple-800 py-2 rounded-lg font-semibold">Subscribe</button>
            <ul class="text-left space-y-3 mt-8 text-sm">
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Single User</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>1GB Storage</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Basic Support</li>
            </ul>
        </div>
         <div class="bg-purple-600 text-white p-8 rounded-lg shadow-lg text-center transform scale-105">
            <h3 class="text-lg font-semibold text-purple-200">STANDARD</h3>
            <p class="text-4xl font-bold my-4">$9.99 <span class="text-lg font-normal text-purple-200">/month</span></p>
            <p class="text-sm text-purple-200 mb-6">$99.99 /year</p>
            <button class="w-full bg-white text-purple-800 py-2 rounded-lg font-semibold">Subscribe</button>
             <ul class="text-left space-y-3 mt-8 text-sm">
                <li class="flex items-center"><svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>2 Users</li>
                <li class="flex items-center"><svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>5GB Storage</li>
                <li class="flex items-center"><svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Standard Support</li>
            </ul>
        </div>
         <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-lg font-semibold text-gray-500">PREMIUM</h3>
            <p class="text-4xl font-bold my-4">$19.99 <span class="text-lg font-normal text-gray-500">/month</span></p>
            <p class="text-sm text-gray-500 mb-6">$199.99 /year</p>
            <button class="w-full bg-purple-200 text-purple-800 py-2 rounded-lg font-semibold">Subscribe</button>
             <ul class="text-left space-y-3 mt-8 text-sm">
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Unlimited Users</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>10GB Storage</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Premium Support</li>
            </ul>
        </div>
    </div>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
?>