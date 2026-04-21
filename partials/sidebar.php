<?php
// File: /partials/sidebar.php
// Purpose: Contains the main sidebar navigation for the application.
// Note: This version uses the BASE_URL constant for all links to ensure they are always correct.

// Get the current page's script name to determine the active link
$current_page   = basename($_SERVER['SCRIPT_NAME']);
$session_role   = $_SESSION['role'] ?? '';
?>
<aside class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="p-6">
        <h1 class="text-2xl font-bold">StudyFlow</h1>
    </div>
    <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
        <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'index.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
            Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/todos.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'todos.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="check-square" class="w-5 h-5 mr-3"></i>
            To-dos
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/study-goals.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'study-goals.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="target" class="w-5 h-5 mr-3"></i>
            Study Goals
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/assignments.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'assignments.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="book-copy" class="w-5 h-5 mr-3"></i>
            Assignments
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/calendar.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'calendar.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
            Calendar
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/ai-tutor.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'ai-tutor.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="brain-circuit" class="w-5 h-5 mr-3"></i>
            AI Tutor
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/code-editor.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'code-editor.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="code-2" class="w-5 h-5 mr-3"></i>
            Code Editor
        </a>

        <div class="pt-4">
            <h2 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">My Desk</h2>
            <div class="space-y-2 mt-2">
                 <a href="<?php echo BASE_URL; ?>/pages/notes.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'notes.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="notebook-tabs" class="w-5 h-5 mr-3"></i>
                    Notes
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/flashcards.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'flashcards.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="layers" class="w-5 h-5 mr-3"></i>
                    Flashcards
                </a>
                 <a href="<?php echo BASE_URL; ?>/pages/sheets.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'sheets.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="table-2" class="w-5 h-5 mr-3"></i>
                    Sheets
                </a>
                 <a href="<?php echo BASE_URL; ?>/pages/resources.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'resources.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="folder-archive" class="w-5 h-5 mr-3"></i>
                    Resources
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/contacts.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'contacts.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="contact" class="w-5 h-5 mr-3"></i>
                    Contacts
                </a>
            </div>
        </div>

         <div class="pt-4">
            <div>
                <button id="shareBtn" class="w-full flex items-center justify-between px-4 py-2 rounded-lg hover:bg-gray-700">
                    <span class="flex items-center">
                        <i data-lucide="share-2" class="w-5 h-5 mr-3"></i> Share
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="shareDropdownIcon"></i>
                </button>
                <div id="shareDropdown" class="hidden mt-2 space-y-2 pl-8">
                    <a href="<?php echo BASE_URL; ?>/pages/share.php" class="block px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">New Share</a>
                    <a href="<?php echo BASE_URL; ?>/pages/shares.php" class="block px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">Shares</a>
                    <a href="<?php echo BASE_URL; ?>/pages/access-logs.php" class="block px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">Access Logs</a>
                </div>
            </div>
            <div>
                <button id="settingsBtn" class="w-full flex items-center justify-between px-4 py-2 rounded-lg hover:bg-gray-700">
                    <span class="flex items-center">
                         <i data-lucide="settings" class="w-5 h-5 mr-3"></i> Settings
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="settingsDropdownIcon"></i>
                </button>
                <div id="settingsDropdown" class="hidden mt-2 space-y-2 pl-8">
                    <?php
                    $role = $_SESSION['role'] ?? '';
                    if (in_array($role, ['admin', 'super_admin'])): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/users.php"
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 text-sm <?php echo ($current_page === 'users.php') ? 'bg-gray-700' : ''; ?>">
                        Users
                    </a>
                    <?php endif; ?>

                    <?php if ($role === 'super_admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/api-settings.php"
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 text-sm flex items-center gap-1.5 <?php echo ($current_page === 'api-settings.php') ? 'bg-gray-700' : ''; ?>">
                        <i data-lucide="key" class="w-3.5 h-3.5 text-red-400"></i> API
                        <span class="ml-auto text-xs bg-red-500/20 text-red-300 rounded px-1">SA</span>
                    </a>
                    <?php endif; ?>

                    <a href="<?php echo BASE_URL; ?>/pages/billing.php"
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 text-sm <?php echo ($current_page === 'billing.php') ? 'bg-gray-700' : ''; ?>">
                        Billing
                    </a>
                </div>
            </div>
            
            <div class="mt-8 border-t border-gray-700 pt-4 mb-6">
                <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center px-4 py-2 rounded-lg text-red-400 hover:bg-red-900/20 transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>
</aside>