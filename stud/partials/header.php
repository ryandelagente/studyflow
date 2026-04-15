<?php
// File: /partials/header.php
// Purpose: Contains the opening HTML and head section.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <?php 
            // Conditionally include the correct sidebar based on user role
            if (isset($_SESSION['role']) && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin')) {
                require_once(BASE_PATH . '/partials/admin_sidebar.php');
            } else {
                require_once(BASE_PATH . '/partials/sidebar.php');
            }
        ?>
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm p-4 flex justify-end items-center">
                <div class="flex items-center space-x-4">
                    <button id="studyTimerBtn" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium w-28 text-center transition-colors duration-200">
                        <span id="timerDisplay">Timer Start</span>
                    </button>
                    <div class="relative group">
                        <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold cursor-pointer">
                            <?php echo htmlspecialchars(strtoupper(substr($_SESSION["username"], 0, 2))); ?>
                        </div>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 hidden group-hover:block">
                            <a href="<?php echo BASE_URL; ?>/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </header>