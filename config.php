<?php
// File: config.php
// Purpose: Main configuration file for database, paths, and API keys.

// --- Path and URL Configuration (Single Source of Truth) ---
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__));
}

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $project_path = str_replace('\\', '/', __DIR__);
    $base_dir = str_replace($doc_root, '', $project_path);
    define('BASE_URL', $protocol . $host . $base_dir);
}

// --- Database Configuration ---
if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
if (!defined('DB_USERNAME')) define('DB_USERNAME', 'root');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', '');
if (!defined('DB_NAME')) define('DB_NAME', 'productivity_hub');

if (!isset($link) || $link === null) {
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
}

// --- Session Flash Message Helpers ---
if (!function_exists('flash_set')) {
    function flash_set(string $type, string $msg): void {
        $_SESSION['_flash'][$type] = $msg;
    }
}
if (!function_exists('flash_get')) {
    function flash_get(string $type): string {
        $msg = $_SESSION['_flash'][$type] ?? '';
        unset($_SESSION['_flash'][$type]);
        return $msg;
    }
}
if (!function_exists('flash_html')) {
    // Renders a dismissible alert. type: 'success' | 'error'
    function flash_html(string $type = 'success'): string {
        $msg = flash_get($type);
        if (!$msg) return '';
        $classes = $type === 'error'
            ? 'bg-red-100 border border-red-400 text-red-700'
            : 'bg-green-100 border border-green-400 text-green-700';
        return '<div class="' . $classes . ' px-4 py-3 rounded mb-4 flex justify-between items-center">'
            . '<span>' . htmlspecialchars($msg) . '</span>'
            . '<button onclick="this.parentElement.remove()" class="ml-4 font-bold">&times;</button>'
            . '</div>';
    }
}

// --- CSRF Protection Helpers ---
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}
if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}

// --- API Key Configuration ---
$secrets_file = __DIR__ . '/secrets.php';
if (file_exists($secrets_file)) {
    require_once $secrets_file;
}
if (!defined('OPENROUTER_API_KEY')) {
    define('OPENROUTER_API_KEY', '');
}
if (!defined('OPENROUTER_MODEL')) {
    define('OPENROUTER_MODEL', 'nvidia/nemotron-3-super-120b-a12b:free');
}
?>