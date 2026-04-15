<?php
// File: config.php
// Purpose: Main configuration file for database, paths, and API keys.

// --- Path and URL Configuration (Single Source of Truth) ---

// Define the absolute server path to the project root.
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__));
}

// --- FIX: Dynamically and reliably determine the BASE_URL ---
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the server's document root and the project's directory path
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $project_path = str_replace('\\', '/', __DIR__);
    
    // Determine the base directory by removing the document root from the project path
    $base_dir = str_replace($doc_root, '', $project_path);

    // Construct the final URL without a trailing slash
    define('BASE_URL', $protocol . $host . $base_dir);
}
// --- END FIX ---


// --- Database Configuration ---
if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
if (!defined('DB_USERNAME')) define('DB_USERNAME', 'root');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', '');
if (!defined('DB_NAME')) define('DB_NAME', 'productivity_hub');

/* Attempt to connect to MySQL database */
if (!isset($link) || $link === null) {
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
}


// --- API Key Configuration ---
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', 'AIzaSyBzlqmZhZNhlZeHp7HkYVg0rCZM-qCh-rw');
}

?>