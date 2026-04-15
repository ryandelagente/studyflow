<?php
// File: /partials/admin_sidebar.php
// Purpose: Sidebar for Super Admin and Admin roles.
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="p-6">
        <h1 class="text-2xl font-bold">StudyBuddy.</h1>
    </div>
    <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'index.php') ? 'bg-gray-700' : ''; ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
            Dashboard
        </a>
        
        <div class="pt-4">
            <h2 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Manage</h2>
            <div class="space-y-2 mt-2">
                 <a href="<?php echo BASE_URL; ?>/admin/workspaces.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'workspaces.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="briefcase" class="w-5 h-5 mr-3"></i>
                    Workspaces
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'users.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    Users
                </a>
                 <a href="<?php echo BASE_URL; ?>/admin/payments.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'payments.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="credit-card" class="w-5 h-5 mr-3"></i>
                    Payments
                </a>
                 <a href="<?php echo BASE_URL; ?>/admin/files.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'files.php') ? 'bg-gray-700' : ''; ?>">
                    <i data-lucide="folder" class="w-5 h-5 mr-3"></i>
                    Files
                </a>
                
                <div>
                    <button id="landingPageBtn" class="w-full flex items-center justify-between px-4 py-2 rounded-lg hover:bg-gray-700">
                        <span class="flex items-center">
                            <i data-lucide="layout-template" class="w-5 h-5 mr-3"></i> Landing Page
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="landingPageDropdownIcon"></i>
                    </button>
                    <div id="landingPageDropdown" class="hidden mt-2 space-y-2 pl-6 text-sm">
                        <a href="<?php echo BASE_URL; ?>/admin/landing-page.php" class="block px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'landing-page.php') ? 'bg-gray-700' : ''; ?>">Manage Sections</a>
                        <a href="<?php echo BASE_URL; ?>/landing.php" target="_blank" class="block px-4 py-2 rounded-lg hover:bg-gray-700">View Landing Page</a>
                        <a href="<?php echo BASE_URL; ?>/admin/privacy-policy.php" class="block px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'privacy-policy.php') ? 'bg-gray-700' : ''; ?>">Edit Privacy Policy</a>
                        <a href="<?php echo BASE_URL; ?>/admin/terms-of-service.php" class="block px-4 py-2 rounded-lg hover:bg-gray-700 <?php echo ($current_page === 'terms-of-service.php') ? 'bg-gray-700' : ''; ?>">Edit Terms of Service</a>
                    </div>
                </div>

                 <a href="#" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700">
                    <i data-lucide="gem" class="w-5 h-5 mr-3"></i>
                    Subscription Plans
                </a>
            </div>
        </div>

         <div class="pt-4">
            <h2 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Setup</h2>
            <div>
                <button id="adminSettingsBtn" class="w-full flex items-center justify-between px-4 py-2 rounded-lg hover:bg-gray-700">
                    <span class="flex items-center">
                         <i data-lucide="settings" class="w-5 h-5 mr-3"></i> Settings
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="settingsDropdownIcon"></i>
                </button>
                <div id="settingsDropdown" class="hidden mt-2 space-y-2 pl-8 text-sm">
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">General Settings</a>
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Email Settings</a>
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Storage Settings</a>
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Super Admins</a>
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Payment Gateways</a>
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Integrations</a>
                    <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">About</a>
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