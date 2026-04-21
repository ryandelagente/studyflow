<?php
// File: /pages/billing.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}

require_once(__DIR__ . '/../config.php');

// ── Fetch current tenant plan ──────────────────────────────
$tenant_id   = (int)($_SESSION['tenant_id'] ?? 0);
$current_plan = 'free';
$tenant_name  = '';

if ($link && $tenant_id > 0) {
    $stmt = mysqli_prepare($link, "SELECT name, plan, status FROM tenants WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $tenant_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $current_plan = $row['plan'] ?: 'free';
        $tenant_name  = $row['name'];
    }
    mysqli_stmt_close($stmt);
}

// ── Plan definitions ───────────────────────────────────────
$plans = [
    [
        'slug'     => 'free',
        'name'     => 'Free',
        'price'    => 0,
        'annual'   => 0,
        'users'    => 1,
        'storage'  => '500 MB',
        'ai'       => false,
        'support'  => 'Community',
        'popular'  => false,
        'color'    => 'gray',
        'features' => [
            'Single user',
            '500 MB storage',
            'All core study tools',
            'Community support',
        ],
        'missing'  => ['AI features', 'Code Editor AI', 'Priority support'],
    ],
    [
        'slug'     => 'basic',
        'name'     => 'Basic',
        'price'    => 4.99,
        'annual'   => 49.99,
        'users'    => 1,
        'storage'  => '1 GB',
        'ai'       => 'limited',
        'support'  => 'Email Support',
        'popular'  => false,
        'color'    => 'blue',
        'features' => [
            'Single user',
            '1 GB storage',
            'All core study tools',
            'Limited AI features',
            'Email support',
        ],
        'missing'  => ['Full AI + Agent mode', 'Team collaboration'],
    ],
    [
        'slug'     => 'standard',
        'name'     => 'Standard',
        'price'    => 9.99,
        'annual'   => 99.99,
        'users'    => 5,
        'storage'  => '5 GB',
        'ai'       => true,
        'support'  => 'Priority Support',
        'popular'  => true,
        'color'    => 'purple',
        'features' => [
            'Up to 5 users',
            '5 GB storage',
            'All core study tools',
            'Full AI + Agent mode',
            'Code Editor AI',
            'Priority support',
        ],
        'missing'  => [],
    ],
    [
        'slug'     => 'premium',
        'name'     => 'Premium',
        'price'    => 19.99,
        'annual'   => 199.99,
        'users'    => -1,
        'storage'  => '10 GB',
        'ai'       => true,
        'support'  => 'Dedicated Support',
        'popular'  => false,
        'color'    => 'yellow',
        'features' => [
            'Unlimited users',
            '10 GB storage',
            'All core study tools',
            'Full AI + Agent mode',
            'Code Editor AI',
            'Dedicated support',
            'Custom integrations',
            'Early access features',
        ],
        'missing'  => [],
    ],
];

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
  <div class="flex justify-between items-center mb-2">
    <div>
      <h2 class="text-2xl font-bold">Billing &amp; Plans</h2>
      <p class="text-gray-500">Settings / Billing — choose the plan that fits your needs</p>
    </div>
  </div>

  <!-- Current plan banner -->
  <div class="bg-white border rounded-lg p-4 mb-8 flex items-center gap-4 shadow-sm">
    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
      <i data-lucide="credit-card" class="w-5 h-5 text-purple-600"></i>
    </div>
    <div class="flex-1">
      <p class="text-sm text-gray-500">Current workspace: <strong><?php echo htmlspecialchars($tenant_name ?: 'Your Workspace'); ?></strong></p>
      <p class="text-sm">Active plan:
        <span id="current-plan-badge" class="inline-block ml-1 text-xs font-bold px-2 py-0.5 rounded-full
          <?php echo match($current_plan) {
              'premium'  => 'bg-yellow-100 text-yellow-800',
              'standard' => 'bg-purple-100 text-purple-800',
              'basic'    => 'bg-blue-100 text-blue-800',
              default    => 'bg-gray-100 text-gray-700',
          }; ?>">
          <?php echo strtoupper($current_plan); ?>
        </span>
      </p>
    </div>
    <p class="text-xs text-gray-400">Plans are updated instantly for demo purposes.</p>
  </div>

  <!-- Plan cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 max-w-6xl">
    <?php foreach ($plans as $p):
      $is_current = ($p['slug'] === $current_plan);
      $is_popular = $p['popular'];
      $card_class = $is_popular
          ? 'bg-purple-600 text-white shadow-xl ring-2 ring-purple-400'
          : 'bg-white text-gray-800 shadow';
      $text_muted = $is_popular ? 'text-purple-200' : 'text-gray-400';
      $check_color = $is_popular ? 'text-white' : 'text-green-500';
      $x_color     = $is_popular ? 'text-purple-300' : 'text-gray-300';
    ?>
    <div class="<?php echo $card_class; ?> p-6 rounded-xl flex flex-col relative" id="plan-card-<?php echo $p['slug']; ?>">
      <?php if ($is_popular): ?>
      <div class="absolute -top-3 left-1/2 -translate-x-1/2">
        <span class="bg-white text-purple-700 text-xs font-bold px-3 py-1 rounded-full shadow">MOST POPULAR</span>
      </div>
      <?php endif; ?>

      <?php if ($is_current): ?>
      <div class="absolute top-3 right-3">
        <span class="text-xs font-bold px-2 py-0.5 rounded-full <?php echo $is_popular ? 'bg-white text-purple-700' : 'bg-green-100 text-green-700'; ?>">
          ✓ Current
        </span>
      </div>
      <?php endif; ?>

      <h3 class="text-base font-bold <?php echo $is_popular ? 'text-purple-200' : 'text-gray-500'; ?> uppercase tracking-wide mb-1">
        <?php echo $p['name']; ?>
      </h3>

      <div class="my-3">
        <?php if ($p['price'] == 0): ?>
          <span class="text-4xl font-extrabold">Free</span>
        <?php else: ?>
          <span class="text-4xl font-extrabold">$<?php echo number_format($p['price'], 2); ?></span>
          <span class="text-sm <?php echo $text_muted; ?>">/mo</span>
          <p class="text-xs <?php echo $text_muted; ?> mt-0.5">$<?php echo number_format($p['annual'], 2); ?> billed annually</p>
        <?php endif; ?>
      </div>

      <ul class="space-y-2 text-sm flex-1 mb-5">
        <?php foreach ($p['features'] as $feat): ?>
        <li class="flex items-start gap-2">
          <i data-lucide="check" class="w-4 h-4 shrink-0 mt-0.5 <?php echo $check_color; ?>"></i>
          <?php echo htmlspecialchars($feat); ?>
        </li>
        <?php endforeach; ?>
        <?php foreach ($p['missing'] as $miss): ?>
        <li class="flex items-start gap-2 opacity-50">
          <i data-lucide="x" class="w-4 h-4 shrink-0 mt-0.5 <?php echo $x_color; ?>"></i>
          <span class="line-through"><?php echo htmlspecialchars($miss); ?></span>
        </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($is_current): ?>
        <button disabled
                class="w-full py-2 rounded-lg font-semibold text-sm
                       <?php echo $is_popular ? 'bg-white/30 text-white cursor-default' : 'bg-gray-100 text-gray-500 cursor-default'; ?>">
          Current Plan
        </button>
      <?php elseif (in_array($_SESSION['role'], ['admin','super_admin'])): ?>
        <button data-plan="<?php echo $p['slug']; ?>"
                onclick="selectPlan(this)"
                class="plan-btn w-full py-2 rounded-lg font-semibold text-sm transition
                       <?php echo $is_popular
                           ? 'bg-white text-purple-700 hover:bg-purple-50'
                           : 'bg-purple-600 text-white hover:bg-purple-700'; ?>">
          <?php echo $p['price'] == 0 ? 'Switch to Free' : 'Subscribe'; ?>
        </button>
      <?php else: ?>
        <button disabled class="w-full py-2 rounded-lg font-semibold text-sm bg-gray-100 text-gray-400 cursor-default">
          Contact Admin
        </button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Feature comparison table -->
  <div class="mt-10 bg-white rounded-xl shadow overflow-hidden max-w-4xl">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-4 font-semibold text-gray-600">Feature</th>
          <th class="p-4 text-center font-semibold text-gray-500">Free</th>
          <th class="p-4 text-center font-semibold text-blue-600">Basic</th>
          <th class="p-4 text-center font-semibold text-purple-600">Standard</th>
          <th class="p-4 text-center font-semibold text-yellow-600">Premium</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $rows = [
            ['Feature', 'Free', 'Basic', 'Standard', 'Premium'],
            ['Users',                  '1',      '1',      '5',           'Unlimited'],
            ['Storage',                '500 MB', '1 GB',   '5 GB',        '10 GB'],
            ['Core study tools',       '✓',      '✓',      '✓',           '✓'],
            ['AI features (limited)',  '✗',      '✓',      '✓',           '✓'],
            ['Full AI + Agent mode',   '✗',      '✗',      '✓',           '✓'],
            ['Code Editor AI',         '✗',      '✗',      '✓',           '✓'],
            ['Team collaboration',     '✗',      '✗',      '✓',           '✓'],
            ['Custom integrations',    '✗',      '✗',      '✗',           '✓'],
            ['Support',                'Community','Email','Priority',    'Dedicated'],
        ];
        foreach (array_slice($rows, 1) as $row):
          $cols = array_slice($row, 1);
        ?>
        <tr class="border-t hover:bg-gray-50">
          <td class="p-4 font-medium text-gray-700"><?php echo $row[0]; ?></td>
          <?php foreach ($cols as $i => $val):
            $is_cur = (isset($plans[$i]) && $plans[$i]['slug'] === $current_plan);
            echo '<td class="p-4 text-center ' . ($is_cur ? 'bg-purple-50 font-semibold' : '') . '">';
            if ($val === '✓') echo '<span class="text-green-500 font-bold">✓</span>';
            elseif ($val === '✗') echo '<span class="text-gray-300">—</span>';
            else echo htmlspecialchars($val);
            echo '</td>';
          endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p class="mt-6 text-xs text-gray-400 max-w-lg">
    All paid plans are for demonstration. In production, integrate Stripe or your preferred payment gateway before enabling real billing.
  </p>
</main>

<!-- Toast notification -->
<div id="billing-toast" style="display:none;"
     class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-semibold
            bg-gray-800 text-white transition-opacity"></div>

<script>
const CSRF = () => window.CSRF_TOKEN || '';

async function selectPlan(btn) {
    const plan = btn.dataset.plan;
    if (!confirm(`Switch to the ${plan.charAt(0).toUpperCase()+plan.slice(1)} plan?`)) return;

    const allBtns = document.querySelectorAll('.plan-btn');
    allBtns.forEach(b => b.disabled = true);
    btn.textContent = '⟳ Updating…';

    try {
        const res  = await fetch(window.APP_BASE_URL + '/api/update-plan.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
            body:    JSON.stringify({ plan }),
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        showToast('✓ Plan updated to ' + plan + '. Refreshing…', '#22c55e');
        setTimeout(() => location.reload(), 1200);
    } catch (e) {
        showToast('✗ ' + e.message, '#ef4444');
        allBtns.forEach(b => b.disabled = false);
        btn.textContent = 'Subscribe';
    }
}

function showToast(msg, color) {
    const t = document.getElementById('billing-toast');
    t.textContent  = msg;
    t.style.background = color;
    t.style.display = 'block';
    t.style.opacity = '1';
    setTimeout(() => {
        t.style.opacity = '0';
        setTimeout(() => t.style.display = 'none', 400);
    }, 3000);
}
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) mysqli_close($link);
?>
