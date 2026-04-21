<?php
// File: /pages/api-settings.php
// Access: super_admin only

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
if ($_SESSION['role'] !== 'super_admin') {
    header('location: ../index.php?error=access_denied'); exit;
}

require_once(__DIR__ . '/../config.php');

$success_message = '';
$error_message   = '';
$secrets_path    = __DIR__ . '/../secrets.php';

// ── Handle save ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_api'])) {
    if (!csrf_verify()) {
        $error_message = "Security token mismatch. Please try again.";
    } else {
        $new_key   = trim($_POST['api_key']  ?? '');
        $new_model = trim($_POST['model']    ?? '');

        if (empty($new_key) || empty($new_model)) {
            $error_message = "Both API key and model are required.";
        } elseif (!str_starts_with($new_key, 'sk-or-')) {
            $error_message = "That doesn't look like a valid OpenRouter API key (should start with sk-or-).";
        } else {
            $content = "<?php\n"
                . "define('OPENROUTER_API_KEY', " . var_export($new_key,   true) . ");\n"
                . "define('OPENROUTER_MODEL',   " . var_export($new_model, true) . ");\n";
            if (file_put_contents($secrets_path, $content) !== false) {
                $success_message = "Settings saved successfully.";
            } else {
                $error_message = "Could not write to secrets.php — check file permissions.";
            }
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');

$current_key   = defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '';
$current_model = defined('OPENROUTER_MODEL')   ? OPENROUTER_MODEL   : '';

// Curated fallback list shown before live fetch
$fallback_models = [
    'nvidia/nemotron-3-super-120b-a12b:free',
    'meta-llama/llama-3.3-70b-instruct:free',
    'google/gemma-3-27b-it:free',
    'mistralai/mistral-7b-instruct:free',
    'qwen/qwen3-8b:free',
    'deepseek/deepseek-r1-0528:free',
    'microsoft/phi-4-reasoning:free',
    'google/gemini-2.0-flash-exp:free',
];
?>

<main class="flex-1 p-8 overflow-y-auto">
  <div class="flex justify-between items-center mb-8">
    <div>
      <h2 class="text-2xl font-bold">API Settings</h2>
      <p class="text-gray-500">Settings / API &mdash; <span class="text-purple-600 font-semibold">Super Admin only</span></p>
    </div>
    <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full">
      <i data-lucide="shield-alert" class="w-3 h-3"></i> super_admin
    </span>
  </div>

  <?php if ($success_message): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i><?php echo htmlspecialchars($success_message); ?>
  </div>
  <?php endif; ?>
  <?php if ($error_message): ?>
  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i><?php echo htmlspecialchars($error_message); ?>
  </div>
  <?php endif; ?>

  <!-- Current status cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white border rounded-lg p-4">
      <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Current API Key</p>
      <p class="font-mono text-sm text-gray-800">
        <?php if ($current_key): ?>
          <?php echo htmlspecialchars(substr($current_key, 0, 16) . str_repeat('&bull;', 20)); ?>
          <span class="ml-2 text-xs text-green-600 font-semibold">&#9679; Configured</span>
        <?php else: ?>
          <span class="text-red-500 font-semibold">Not configured</span>
        <?php endif; ?>
      </p>
    </div>
    <div class="bg-white border rounded-lg p-4">
      <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Active Model</p>
      <p class="font-mono text-sm text-gray-800 break-all">
        <?php if ($current_model): ?>
          <?php echo htmlspecialchars($current_model); ?>
        <?php else: ?>
          <span class="text-red-500 font-semibold">Not set</span>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <!-- Edit form -->
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <h3 class="text-lg font-semibold mb-1">Update API Configuration</h3>
    <p class="text-sm text-gray-500 mb-4">
      Get your free API key from
      <a href="https://openrouter.ai/keys" target="_blank" class="text-purple-600 hover:underline">openrouter.ai/keys</a>.
      Free-tier keys work with all <code class="text-xs bg-gray-100 px-1 rounded">:free</code> models below.
    </p>

    <form method="POST">
      <?php echo csrf_field(); ?>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">OpenRouter API Key</label>
        <input type="text" name="api_key"
               value="<?php echo htmlspecialchars($current_key); ?>"
               placeholder="sk-or-v1-..."
               autocomplete="off"
               class="w-full border rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" required>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
        <input type="text" name="model" id="modelInput"
               value="<?php echo htmlspecialchars($current_model); ?>"
               placeholder="e.g. meta-llama/llama-3.3-70b-instruct:free"
               class="w-full border rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" required>

        <!-- Quick picks header -->
        <div class="flex items-center justify-between mt-3 mb-2">
          <p class="text-xs text-gray-500 font-semibold">Free model quick-picks:</p>
          <button type="button" id="btn-fetch-models"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5
                         bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i data-lucide="refresh-cw" class="w-3 h-3" id="fetch-icon"></i>
            Fetch Latest Free Models
          </button>
        </div>

        <!-- Model chips -->
        <div id="model-chips" class="flex flex-wrap gap-2">
          <?php foreach ($fallback_models as $m): ?>
          <button type="button"
                  data-model="<?php echo htmlspecialchars($m); ?>"
                  onclick="pickModel(this,'<?php echo $m; ?>')"
                  class="model-chip text-xs px-2 py-1 border rounded-lg font-mono transition
                         <?php echo ($m === $current_model)
                            ? 'bg-purple-100 border-purple-500 text-purple-800'
                            : 'bg-gray-50 text-gray-700 hover:bg-purple-50 hover:border-purple-400'; ?>">
            <?php echo htmlspecialchars($m); ?>
          </button>
          <?php endforeach; ?>
        </div>

        <div id="fetch-status" class="text-xs mt-1 hidden"></div>
      </div>

      <button type="submit" name="save_api"
              class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 font-semibold">
        Save Changes
      </button>
    </form>
  </div>

  <!-- AI Features overview -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">AI Features Using This Key</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      $features = [
          ['page' => 'AI Tutor',    'desc' => 'Multi-turn chat with history',        'endpoint' => 'api/gemini-api.php',      'icon' => 'brain-circuit'],
          ['page' => 'Notes',       'desc' => 'Generate note content from a prompt', 'endpoint' => 'api/ai-assist.php',       'icon' => 'notebook-tabs'],
          ['page' => 'Flashcards',  'desc' => 'Generate Q&A pairs from a topic',     'endpoint' => 'api/ai-assist.php',       'icon' => 'layers'],
          ['page' => 'Assignments', 'desc' => 'Generate project descriptions',       'endpoint' => 'api/ai-assist.php',       'icon' => 'book-copy'],
          ['page' => 'Study Goals', 'desc' => 'Suggest goals for a subject',         'endpoint' => 'api/ai-assist.php',       'icon' => 'target'],
          ['page' => 'To-dos',      'desc' => 'Break down projects into tasks',      'endpoint' => 'api/ai-assist.php',       'icon' => 'check-square'],
          ['page' => 'Code Editor', 'desc' => 'Inline AI + Agent mode',              'endpoint' => 'api/editor/ai-agent.php', 'icon' => 'code-2'],
      ];
      foreach ($features as $f): ?>
      <div class="border rounded-lg p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
          <i data-lucide="<?php echo $f['icon']; ?>" class="w-5 h-5 text-purple-600"></i>
        </div>
        <div>
          <p class="font-semibold text-sm"><?php echo $f['page']; ?></p>
          <p class="text-xs text-gray-500"><?php echo $f['desc']; ?></p>
          <p class="text-xs font-mono text-gray-400 mt-0.5"><?php echo $f['endpoint']; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .9s linear infinite; }
</style>

<script>
const CSRF = () => window.CSRF_TOKEN || '';

// ── Pick a model chip ─────────────────────────────────────
function pickModel(el, modelId) {
    document.getElementById('modelInput').value = modelId;
    document.querySelectorAll('.model-chip').forEach(c => {
        c.className = c.className
            .replace('bg-purple-100 border-purple-500 text-purple-800', '')
            .replace('bg-gray-50 text-gray-700', '')
            .trim() + ' bg-gray-50 text-gray-700 hover:bg-purple-50 hover:border-purple-400';
    });
    el.className = el.className
        .replace('bg-gray-50 text-gray-700 hover:bg-purple-50 hover:border-purple-400','')
        .trim() + ' bg-purple-100 border-purple-500 text-purple-800';
}

// ── Fetch latest free models ──────────────────────────────
document.getElementById('btn-fetch-models').addEventListener('click', async () => {
    const btn    = document.getElementById('btn-fetch-models');
    const icon   = document.getElementById('fetch-icon');
    const status = document.getElementById('fetch-status');
    const chips  = document.getElementById('model-chips');

    btn.disabled = true;
    icon.classList.add('spin');
    status.textContent = 'Fetching from openrouter.ai…';
    status.className   = 'text-xs mt-1 text-gray-400';
    status.classList.remove('hidden');

    try {
        const res  = await fetch(window.APP_BASE_URL + '/api/get-free-models.php', {
            headers: { 'X-CSRF-TOKEN': CSRF() }
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        const currentModel = document.getElementById('modelInput').value;
        chips.innerHTML = '';

        data.models.forEach(m => {
            const b = document.createElement('button');
            b.type      = 'button';
            b.className = 'model-chip text-xs px-2 py-1 border rounded-lg font-mono transition ' +
                (m.id === currentModel
                    ? 'bg-purple-100 border-purple-500 text-purple-800'
                    : 'bg-gray-50 text-gray-700 hover:bg-purple-50 hover:border-purple-400');
            b.title       = [m.name, m.context ? m.context + ' ctx' : '', m.description].filter(Boolean).join(' · ');
            b.textContent = m.id;
            b.addEventListener('click', () => pickModel(b, m.id));
            chips.appendChild(b);
        });

        status.textContent = `✓ ${data.total} free models loaded (as of ${data.fetched_at})`;
        status.className   = 'text-xs mt-1 text-green-600';
    } catch (e) {
        status.textContent = '✗ ' + e.message;
        status.className   = 'text-xs mt-1 text-red-500';
    } finally {
        btn.disabled = false;
        icon.classList.remove('spin');
    }
});
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) mysqli_close($link);
?>
