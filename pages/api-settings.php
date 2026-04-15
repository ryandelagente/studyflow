<?php
// File: /pages/api-settings.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('location: ../index.php'); exit;
}

require_once(__DIR__ . '/../config.php');

$success_message = '';
$error_message   = '';
$secrets_path    = __DIR__ . '/../secrets.php';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_api'])) {
    $new_key   = trim($_POST['api_key'] ?? '');
    $new_model = trim($_POST['model'] ?? '');

    if (empty($new_key) || empty($new_model)) {
        $error_message = "Both API key and model are required.";
    } else {
        // Validate key starts with sk-or-
        if (!str_starts_with($new_key, 'sk-or-')) {
            $error_message = "That doesn't look like a valid OpenRouter API key (should start with sk-or-).";
        } else {
            $content = "<?php\n"
                . "define('OPENROUTER_API_KEY', " . var_export($new_key, true) . ");\n"
                . "define('OPENROUTER_MODEL',   " . var_export($new_model, true) . ");\n";
            if (file_put_contents($secrets_path, $content) !== false) {
                $success_message = "Settings saved. Reload the page to see the updated values.";
            } else {
                $error_message = "Could not write to secrets.php — check file permissions.";
            }
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');

$current_key   = defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '';
$current_model = defined('OPENROUTER_MODEL')   ? OPENROUTER_MODEL   : '';

// Free models available on OpenRouter (curated list)
$known_models = [
    'nvidia/nemotron-3-super-120b-a12b:free',
    'meta-llama/llama-3.3-70b-instruct:free',
    'google/gemma-3-27b-it:free',
    'mistralai/mistral-7b-instruct:free',
    'qwen/qwen3-8b:free',
    'deepseek/deepseek-r1-0528:free',
];
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">API Settings</h2>
            <p class="text-gray-500">Settings / API</p>
        </div>
    </div>

    <?php if ($success_message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Current status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Current API Key</p>
            <p class="font-mono text-sm text-gray-800">
                <?php echo $current_key
                    ? htmlspecialchars(substr($current_key, 0, 12) . str_repeat('•', 20))
                    : '<span class="text-red-500">Not configured</span>'; ?>
            </p>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Active Model</p>
            <p class="font-mono text-sm text-gray-800"><?php echo $current_model ? htmlspecialchars($current_model) : '<span class="text-red-500">Not set</span>'; ?></p>
        </div>
    </div>

    <!-- Edit form -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-1">Update API Configuration</h3>
        <p class="text-sm text-gray-500 mb-4">Get your API key from <a href="https://openrouter.ai/keys" target="_blank" class="text-purple-600 hover:underline">openrouter.ai/keys</a>.</p>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">OpenRouter API Key</label>
                <input type="text" name="api_key" value="<?php echo htmlspecialchars($current_key); ?>"
                       placeholder="sk-or-v1-..."
                       class="w-full border rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                <input type="text" name="model" id="modelInput" value="<?php echo htmlspecialchars($current_model); ?>"
                       placeholder="e.g. nvidia/nemotron-3-super-120b-a12b:free"
                       class="w-full border rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                <p class="text-xs text-gray-400 mt-1">Or pick a known free model:</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <?php foreach ($known_models as $m): ?>
                    <button type="button" onclick="document.getElementById('modelInput').value='<?php echo $m; ?>'"
                            class="text-xs px-2 py-1 border rounded hover:bg-purple-50 hover:border-purple-400 font-mono">
                        <?php echo htmlspecialchars($m); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" name="save_api" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 font-semibold">Save Changes</button>
        </form>
    </div>

    <!-- AI Features list -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">AI Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $features = [
                ['page' => 'AI Tutor',    'desc' => 'Multi-turn chat with history',        'endpoint' => 'api/gemini-api.php', 'icon' => 'brain-circuit'],
                ['page' => 'Notes',       'desc' => 'Generate note content from a prompt', 'endpoint' => 'api/ai-assist.php',  'icon' => 'notebook-tabs'],
                ['page' => 'Flashcards',  'desc' => 'Generate Q&A pairs from a topic',     'endpoint' => 'api/ai-assist.php',  'icon' => 'layers'],
                ['page' => 'Assignments', 'desc' => 'Generate project descriptions',       'endpoint' => 'api/ai-assist.php',  'icon' => 'book-copy'],
                ['page' => 'Study Goals', 'desc' => 'Suggest goals for a subject',         'endpoint' => 'api/ai-assist.php',  'icon' => 'target'],
                ['page' => 'To-dos',      'desc' => 'Break down projects into tasks',      'endpoint' => 'api/ai-assist.php',  'icon' => 'check-square'],
            ];
            foreach ($features as $f): ?>
            <div class="border rounded-lg p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                    <i data-lucide="<?php echo $f['icon']; ?>" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm"><?php echo $f['page']; ?></p>
                    <p class="text-xs text-gray-500"><?php echo $f['desc']; ?></p>
                    <p class="text-xs font-mono text-gray-400 mt-1"><?php echo $f['endpoint']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) mysqli_close($link);
?>
