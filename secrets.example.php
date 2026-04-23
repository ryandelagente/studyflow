<?php
// File: secrets.example.php
// Purpose: Template for secrets.php — copy this file to secrets.php and fill in your values.
//
// IMPORTANT: Never commit secrets.php to version control.
//            It is already listed in .gitignore.
//
// -------------------------------------------------------------------------
// OpenRouter AI API
// -------------------------------------------------------------------------
// Get your key at https://openrouter.ai/keys (free tier available).
if (!defined('OPENROUTER_API_KEY')) {
    define('OPENROUTER_API_KEY', 'YOUR_OPENROUTER_API_KEY_HERE');
}

// Optional: override the default AI model.
// Any model slug from https://openrouter.ai/models works.
// Free models have ":free" appended, e.g. "nvidia/nemotron-3-super-120b-a12b:free".
// if (!defined('OPENROUTER_MODEL')) {
//     define('OPENROUTER_MODEL', 'nvidia/nemotron-3-super-120b-a12b:free');
// }

// -------------------------------------------------------------------------
// Database credentials (optional — override the defaults in config.php)
// -------------------------------------------------------------------------
// Uncomment and fill in these lines if you want to keep DB credentials out
// of config.php and in this secrets file instead.
//
// if (!defined('DB_SERVER'))   define('DB_SERVER',   'localhost');
// if (!defined('DB_USERNAME')) define('DB_USERNAME', 'your_db_user');
// if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'your_db_password');
// if (!defined('DB_NAME'))     define('DB_NAME',     'productivity_hub');
