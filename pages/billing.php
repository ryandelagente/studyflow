<?php
// File: /pages/billing.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php');
    exit;
}

require_once(__DIR__ . '/../config.php');

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold">Billing</h2>
        <p class="text-gray-500">Settings / Billing — choose a plan that fits your needs</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl">
        <!-- Basic Plan -->
        <div class="bg-white p-8 rounded-lg shadow text-center flex flex-col">
            <h3 class="text-lg font-semibold text-gray-500">BASIC</h3>
            <p class="text-4xl font-bold my-4">$4.99 <span class="text-lg font-normal text-gray-500">/mo</span></p>
            <p class="text-sm text-gray-400 mb-6">$49.99 billed annually</p>
            <ul class="text-left space-y-3 text-sm mb-8 flex-1">
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>Single User</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>1 GB Storage</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>AI Tutor (limited)</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>Basic Support</li>
            </ul>
            <button class="w-full bg-purple-100 text-purple-800 py-2 rounded-lg font-semibold hover:bg-purple-200">Subscribe</button>
        </div>

        <!-- Standard Plan (highlighted) -->
        <div class="bg-purple-600 text-white p-8 rounded-lg shadow-xl text-center flex flex-col scale-105">
            <div class="text-xs font-bold bg-white text-purple-600 rounded-full px-3 py-1 inline-block mb-2 self-center">MOST POPULAR</div>
            <h3 class="text-lg font-semibold text-purple-200">STANDARD</h3>
            <p class="text-4xl font-bold my-4">$9.99 <span class="text-lg font-normal text-purple-200">/mo</span></p>
            <p class="text-sm text-purple-300 mb-6">$99.99 billed annually</p>
            <ul class="text-left space-y-3 text-sm mb-8 flex-1">
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 shrink-0"></i>Up to 5 Users</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 shrink-0"></i>5 GB Storage</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 shrink-0"></i>Full AI Features</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 shrink-0"></i>Priority Support</li>
            </ul>
            <button class="w-full bg-white text-purple-700 py-2 rounded-lg font-semibold hover:bg-purple-50">Subscribe</button>
        </div>

        <!-- Premium Plan -->
        <div class="bg-white p-8 rounded-lg shadow text-center flex flex-col">
            <h3 class="text-lg font-semibold text-gray-500">PREMIUM</h3>
            <p class="text-4xl font-bold my-4">$19.99 <span class="text-lg font-normal text-gray-500">/mo</span></p>
            <p class="text-sm text-gray-400 mb-6">$199.99 billed annually</p>
            <ul class="text-left space-y-3 text-sm mb-8 flex-1">
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>Unlimited Users</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>10 GB Storage</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>Full AI Features</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>Dedicated Support</li>
                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i>Custom Integrations</li>
            </ul>
            <button class="w-full bg-purple-100 text-purple-800 py-2 rounded-lg font-semibold hover:bg-purple-200">Subscribe</button>
        </div>
    </div>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) mysqli_close($link);
?>
