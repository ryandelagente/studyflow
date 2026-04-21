<?php
// /pages/study-goals.php

// --- SETUP ---
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
$db_error = null;
$success_message = null;
$study_goals = [];
$categories = [];

// --- DATABASE CONNECTION CHECK ---
if (!isset($link) || !$link) {
    $db_error = "Database connection failed. Please check your config.php settings.";
}

// --- CRUD LOGIC FOR GOALS AND CATEGORIES ---
if (!$db_error && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify()) { $db_error = "Security token mismatch. Please try again."; goto render; }

    // --- GOAL ACTIONS ---
    // AI BULK CREATE GOALS
    if (isset($_POST['ai_add_goals'])) {
        $goals_json = $_POST['selected_goals'] ?? '';
        $goals = json_decode($goals_json, true);
        $finish_by = date('Y-m-d', strtotime('+30 days'));
        if ($goals && is_array($goals)) {
            $sql = "INSERT INTO study_goals (user_id, tenant_id, title, description, finish_by) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                foreach ($goals as $g) {
                    $t = trim($g['title'] ?? '');
                    $d = trim($g['description'] ?? '');
                    if (!empty($t)) {
                        mysqli_stmt_bind_param($stmt, "iisss", $user_id, $tenant_id, $t, $d, $finish_by);
                        mysqli_stmt_execute($stmt);
                    }
                }
                mysqli_stmt_close($stmt);
                header("location: study-goals.php?success=goals_added");
                exit();
            }
        }
    }

    // CREATE GOAL
    if (isset($_POST['add_goal'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $finish_by = trim($_POST['finish_by']);
        
        $sql = "INSERT INTO study_goals (user_id, tenant_id, title, description, category, finish_by) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iissss", $user_id, $tenant_id, $title, $description, $category, $finish_by);
            if (mysqli_stmt_execute($stmt)) {
                header("location: study-goals.php?success=goal_added");
                exit();
            } else { $db_error = "Error creating goal: " . mysqli_stmt_error($stmt); }
        }
    }

    // UPDATE GOAL
    if (isset($_POST['edit_goal'])) {
        $goal_id = $_POST['goal_id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $finish_by = trim($_POST['finish_by']);

        $sql = "UPDATE study_goals SET title = ?, description = ?, category = ?, finish_by = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssiii", $title, $description, $category, $finish_by, $goal_id, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: study-goals.php?success=goal_updated");
                exit();
            } else { $db_error = "Error updating goal."; }
        }
    }

    // TOGGLE GOAL COMPLETION
    if (isset($_POST['toggle_complete_goal'])) {
        $goal_id = $_POST['goal_id'];
        $is_completed = $_POST['is_completed'] ? 0 : 1; // Toggle
        $sql = "UPDATE study_goals SET is_completed = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiii", $is_completed, $goal_id, $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            header("location: study-goals.php");
            exit();
        }
    }

    // DELETE GOAL
    if (isset($_POST['delete_goal'])) {
        $goal_id = $_POST['goal_id'];
        $sql = "DELETE FROM study_goals WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $goal_id, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: study-goals.php?success=goal_deleted");
                exit();
            } else { $db_error = "Error deleting goal."; }
        }
    }

    // --- CATEGORY ACTIONS ---
    // CREATE CATEGORY
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $sql = "INSERT INTO goal_categories (user_id, name) VALUES (?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "is", $user_id, $name);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: study-goals.php?success=cat_added");
                    exit();
                } else { $db_error = "Error adding category. It might already exist."; }
            }
        }
    }
    
    // UPDATE CATEGORY
    if (isset($_POST['update_category'])) {
        $category_id = trim($_POST['category_id']);
        $new_name = trim($_POST['name']);
        if (!empty($category_id) && !empty($new_name)) {
            $sql = "UPDATE goal_categories SET name = ? WHERE id = ? AND user_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sii", $new_name, $category_id, $user_id);
                if(mysqli_stmt_execute($stmt)) {
                     header("Location: study-goals.php?success=cat_updated");
                     exit();
                } else { $db_error = "Error updating category."; }
            }
        }
    }

    // DELETE CATEGORY
    if (isset($_POST['delete_category'])) {
        $category_id = trim($_POST['category_id']);
        if(!empty($category_id)) {
            $sql = "DELETE FROM goal_categories WHERE id = ? AND user_id = ?";
             if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $category_id, $user_id);
                if(mysqli_stmt_execute($stmt)) {
                     header("Location: study-goals.php?success=cat_deleted");
                     exit();
                } else { $db_error = "Error deleting category."; }
            }
        }
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    $messages = [
        'goal_added'   => 'Goal added successfully.',
        'goal_updated' => 'Goal updated successfully.',
        'goal_deleted' => 'Goal deleted successfully.',
        'cat_added'    => 'Category added successfully.',
        'cat_updated'  => 'Category updated successfully.',
        'cat_deleted'  => 'Category deleted successfully.',
        'goals_added'  => 'AI-generated goals added successfully.',
    ];
    $success_message = $messages[$_GET['success']] ?? 'Action successful.';
}

// --- RENDER PAGE ---
render:
// READ DATA — runs after label so data is always available even after goto
if (!$db_error && $link) {
    $sql_cat = "SELECT id, name FROM goal_categories WHERE user_id = ? ORDER BY name ASC";
    if ($stmt_cat = mysqli_prepare($link, $sql_cat)) {
        mysqli_stmt_bind_param($stmt_cat, "i", $user_id);
        mysqli_stmt_execute($stmt_cat);
        $result = mysqli_stmt_get_result($stmt_cat);
        while ($row = mysqli_fetch_assoc($result)) { $categories[] = $row; }
        mysqli_stmt_close($stmt_cat);
    }

    $sql_goals = "SELECT id, title, description, category, finish_by, is_completed, created_at FROM study_goals WHERE user_id = ? AND tenant_id = ? ORDER BY created_at DESC";
    if ($stmt_goals = mysqli_prepare($link, $sql_goals)) {
        mysqli_stmt_bind_param($stmt_goals, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt_goals);
        $result = mysqli_stmt_get_result($stmt_goals);
        while ($row = mysqli_fetch_assoc($result)) { $study_goals[] = $row; }
        mysqli_stmt_close($stmt_goals);
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>
<style>
    .goal-description {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease-out, margin 0.3s ease-out;
    }
    .goal-header.active-goal + .goal-description {
        max-height: 500px; /* Adjust as needed */
        margin-top: 1rem;
        transition: max-height 0.5s ease-in, margin 0.5s ease-in;
    }
</style>
            
<main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">Study Goals</h2>
            <p class="text-gray-500">Manage your long-term study objectives</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="openModal('category-manager-modal')" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300">Categories</button>
            <button onclick="openModal('ai-goals-modal')" class="bg-green-100 text-green-800 px-6 py-2 rounded-lg font-semibold hover:bg-green-200"><i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>AI Suggest</button>
            <button onclick="openModal('add-goal-modal')" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">Create New Studygoal</button>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($db_error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><strong>Error:</strong> <?php echo $db_error; ?></div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php if (empty($study_goals) && !$db_error): ?>
            <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">No study goals found. Create one to get started!</div>
        <?php else: ?>
            <?php foreach ($study_goals as $goal): ?>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="goal-header flex items-start cursor-pointer" onclick="toggleGoalView(this)">
                    <form method="post" class="mr-4 mt-1" onclick="event.stopPropagation();">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                        <input type="hidden" name="is_completed" value="<?php echo $goal['is_completed']; ?>">
                        <button type="submit" name="toggle_complete_goal" class="w-6 h-6 border-2 <?php echo $goal['is_completed'] ? 'bg-purple-600 border-purple-600' : 'border-gray-300'; ?> rounded-md flex items-center justify-center">
                            <?php if ($goal['is_completed']): ?><i data-lucide="check" class="w-4 h-4 text-white"></i><?php endif; ?>
                        </button>
                    </form>
                    <div class="flex-1">
                        <div class="flex items-center mb-1">
                            <h3 class="font-bold text-lg <?php echo $goal['is_completed'] ? 'line-through text-gray-500' : 'text-gray-900'; ?>"><?php echo htmlspecialchars($goal['title']); ?></h3>
                            <?php if(!empty($goal['category'])): ?>
                            <span class="ml-3 text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800"><?php echo htmlspecialchars($goal['category']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center" onclick="event.stopPropagation();">
                        <button class="text-gray-400 hover:text-blue-500 mr-2 edit-goal-btn"
                            data-id="<?php echo $goal['id']; ?>"
                            data-title="<?php echo htmlspecialchars($goal['title']); ?>"
                            data-description="<?php echo htmlspecialchars($goal['description']); ?>"
                            data-category="<?php echo htmlspecialchars($goal['category']); ?>"
                            data-finish-by="<?php echo $goal['finish_by']; ?>">
                            <i data-lucide="pencil" class="w-5 h-5"></i>
                        </button>
                        <form method="post" onsubmit="return confirm('Are you sure?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                            <button type="submit" name="delete_goal" class="text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                        </form>
                    </div>
                </div>
                <div class="goal-description">
                     <div class="p-6 rounded-lg bg-gradient-to-r from-purple-500 to-indigo-500 text-white">
                        <p><?php echo nl2br(htmlspecialchars($goal['description'] ?: 'No description provided.')); ?></p>
                     </div>
                     <div class="text-sm text-gray-500 mt-4">
                        <span>Created: <span class="font-semibold bg-green-100 text-green-800 px-2 py-1 rounded"><?php echo date("d M Y", strtotime($goal['created_at'])); ?></span></span> |
                        <span>Finish by: <span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded"><?php echo date("d M Y", strtotime($goal['finish_by'])); ?></span></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<div id="add-goal-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Create a New Study Goal</h3>
        <form method="post"><?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium">Goal Title</label>
                <input type="text" name="title" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border rounded-md p-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Category</label>
                <select name="category" class="mt-1 block w-full border rounded-md p-2">
                    <option value="">Uncategorized</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Finish By</label>
                <input type="date" name="finish_by" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('add-goal-modal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="add_goal" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Add Goal</button>
            </div>
        </form>
    </div>
</div>

<div id="edit-goal-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Edit Study Goal</h3>
        <form method="post"><?php echo csrf_field(); ?>
            <input type="hidden" name="goal_id" id="edit_goal_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Goal Title</label>
                <input type="text" name="title" id="edit_title" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" id="edit_description" rows="3" class="mt-1 block w-full border rounded-md p-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Category</label>
                <select name="category" id="edit_category" class="mt-1 block w-full border rounded-md p-2">
                    <option value="">Uncategorized</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Finish By</label>
                <input type="date" name="finish_by" id="edit_finish_by" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('edit-goal-modal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="edit_goal" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="category-manager-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Manage Categories</h3>
        <div class="space-y-2 mb-6 max-h-60 overflow-y-auto">
            <?php foreach($categories as $cat): ?>
            <div class="flex justify-between items-center bg-gray-50 p-2 rounded-md">
                <span id="cat-name-<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></span>
                <div class="flex items-center space-x-2">
                    <button onclick="showEditCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>')" class="text-gray-400 hover:text-blue-500"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this category?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" name="delete_category" class="text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <form method="post" id="add-category-form" class="flex space-x-2">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" class="flex-1 border rounded-md p-2" placeholder="New category name..." required>
            <button type="submit" name="add_category" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold">Add</button>
        </form>
        <form method="post" id="edit-category-form" class="hidden space-x-2">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="category_id" id="edit_category_id">
            <input type="text" name="name" id="edit_category_name" class="flex-1 border rounded-md p-2" required>
            <button type="submit" name="update_category" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Save</button>
            <button type="button" onclick="hideEditCategory()" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
        </form>
        <div class="text-right mt-6">
            <button type="button" onclick="closeModal('category-manager-modal')" class="bg-gray-200 px-4 py-2 rounded-lg">Close</button>
        </div>
    </div>
</div>

<script>
    // --- Modal Handling ---
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }
    
    // --- Goal Modals ---
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.edit-goal-btn').forEach(button => {
            button.addEventListener('click', function () {
                const data = this.dataset;
                document.getElementById('edit_goal_id').value = data.id;
                document.getElementById('edit_title').value = data.title;
                document.getElementById('edit_description').value = data.description;
                document.getElementById('edit_finish_by').value = data.finishBy;
                const categorySelect = document.getElementById('edit_category');
                categorySelect.value = data.category;
                openModal('edit-goal-modal');
            });
        });
    });

    // --- Category Manager ---
    function showEditCategory(id, name) {
        document.getElementById('add-category-form').classList.add('hidden');
        document.getElementById('edit-category-form').classList.remove('hidden');
        document.getElementById('edit-category-id').value = id;
        document.getElementById('edit_category_name').value = name;
    }

    function hideEditCategory() {
        document.getElementById('add-category-form').classList.remove('hidden');
        document.getElementById('edit-category-form').classList.add('hidden');
    }
    
    // --- AI Suggest Goals ---
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('ai-goals-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const subject = document.getElementById('ai-goals-subject').value.trim();
            const count   = document.getElementById('ai-goals-count').value;
            const btn     = document.getElementById('ai-goals-btn');
            const errDiv  = document.getElementById('ai-goals-error');
            errDiv.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Generating...';
            document.getElementById('ai-goals-preview').classList.add('hidden');

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/ai-assist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
                    body: JSON.stringify({ feature: 'goals_suggest', context: subject, count: parseInt(count) })
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                const goals = JSON.parse(data.result);
                if (!Array.isArray(goals) || goals.length === 0) throw new Error('No goals were generated.');

                const container = document.getElementById('ai-goals-list');
                container.innerHTML = '';
                goals.forEach((g, i) => {
                    container.innerHTML += `<label class="flex items-start gap-2 p-3 border rounded cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="ai_goals[]" value="${i}" checked class="mt-1">
                        <div><p class="font-semibold text-sm">${g.title}</p><p class="text-xs text-gray-500">${g.description}</p></div>
                    </label>`;
                });
                document.getElementById('ai-goals-data').value = JSON.stringify(goals);
                document.getElementById('ai-goals-preview').classList.remove('hidden');
            } catch (err) {
                errDiv.textContent = 'Error: ' + err.message;
                errDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Generate';
            }
        });

        document.getElementById('ai-goals-save-form').addEventListener('submit', function() {
            const checked  = document.querySelectorAll('input[name="ai_goals[]"]:checked');
            const allGoals = JSON.parse(document.getElementById('ai-goals-data').value);
            const selected = Array.from(checked).map(cb => allGoals[parseInt(cb.value)]);
            document.getElementById('ai-goals-selected').value = JSON.stringify(selected);
        });
    });

    // --- NEW INLINE VIEW SCRIPT ---
    let activeGoal = null;
    function toggleGoalView(goalHeader) {
        if (activeGoal && activeGoal !== goalHeader) {
            activeGoal.classList.remove('active-goal');
        }

        goalHeader.classList.toggle('active-goal');

        if (goalHeader.classList.contains('active-goal')) {
            activeGoal = goalHeader;
        } else {
            activeGoal = null;
        }
    }
</script>

<!-- AI Suggest Goals Modal -->
<div id="ai-goals-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-green-600"></i> AI Suggest Study Goals</h2>
        <form id="ai-goals-form">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject or Topic</label>
                <input type="text" id="ai-goals-subject" class="w-full border rounded-md p-2" placeholder="e.g. Machine Learning, Spanish Language, Organic Chemistry..." required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Goals</label>
                <select id="ai-goals-count" class="w-full border rounded-md p-2">
                    <option value="3">3 goals</option>
                    <option value="5" selected>5 goals</option>
                </select>
            </div>
            <div id="ai-goals-error" class="hidden bg-red-100 text-red-700 px-3 py-2 rounded mb-4 text-sm"></div>
            <div class="flex justify-end gap-2 mb-4">
                <button type="button" onclick="closeModal('ai-goals-modal')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" id="ai-goals-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Generate</button>
            </div>
        </form>
        <div id="ai-goals-preview" class="hidden">
            <h3 class="font-semibold text-gray-700 mb-2">Select goals to add</h3>
            <form id="ai-goals-save-form" method="POST">
                <?php echo csrf_field(); ?>
                <div id="ai-goals-list" class="space-y-2 mb-4 max-h-60 overflow-y-auto"></div>
                <input type="hidden" name="selected_goals" id="ai-goals-selected">
                <input type="hidden" id="ai-goals-data" value="[]">
                <div class="flex justify-end">
                    <button type="submit" name="ai_add_goals" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Add Selected Goals</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// --- CLEANUP ---
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>