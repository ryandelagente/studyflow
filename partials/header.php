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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
    <script>window.APP_BASE_URL = '<?php echo rtrim(BASE_URL, '/'); ?>';
    window.CSRF_TOKEN = '<?php echo csrf_token(); ?>';</script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <?php 
            // This uses the reliable server path from config.php
            require_once(BASE_PATH . '/partials/sidebar.php'); 
        ?>
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm p-4 flex justify-end items-center">
                <div class="flex items-center space-x-4">
                    <button id="studyTimerBtn" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium w-28 text-center transition-colors duration-200">
                        <span id="timerDisplay">Timer Start</span>
                    </button>
                    <?php
                        $initials = 'U';
                        if (!empty($_SESSION['username'])) {
                            $parts = explode(' ', trim($_SESSION['username']));
                            if (count($parts) >= 2) {
                                $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($parts[0], 0, 2));
                            }
                        }
                    ?>
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold"><?php echo htmlspecialchars($initials); ?></div>
                </div>
            </header>

