<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// /pages/study-goals.php

// --- SETUP ---
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id = $_SESSION["id"]; 
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session
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
    
    // --- GOAL ACTIONS ---
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

        $sql = "UPDATE study_goals SET title = ?, description = ?, category = ?, finish_by = ? WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssii", $title, $description, $category, $finish_by, $goal_id, $tenant_id);
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
        $sql = "UPDATE study_goals SET is_completed = ? WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $is_completed, $goal_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            header("location: study-goals.php");
            exit();
        }
    }

    // DELETE GOAL
    if (isset($_POST['delete_goal'])) {
        $goal_id = $_POST['goal_id'];
        $sql = "DELETE FROM study_goals WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $goal_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: study-goals.php?success=goal_deleted");
                exit();
            } else { $db_error = "Error deleting goal."; }
        }
    }

    // --- CATEGORY ACTIONS (These are user-specific, not tenant-specific in this design) ---
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
        'goal_added' => 'Goal added successfully.',
        'goal_updated' => 'Goal updated successfully.',
        'goal_deleted' => 'Goal deleted successfully.',
        'cat_added' => 'Category added successfully.',
        'cat_updated' => 'Category updated successfully.',
        'cat_deleted' => 'Category deleted successfully.'
    ];
    $success_message = $messages[$_GET['success']] ?? 'Action successful.';
}

// --- READ DATA ---
if (!$db_error && $link) {
    // Fetch categories for the current user
    $sql_cat = "SELECT id, name FROM goal_categories WHERE user_id = ? ORDER BY name ASC";
    if ($stmt_cat = mysqli_prepare($link, $sql_cat)) {
        mysqli_stmt_bind_param($stmt_cat, "i", $user_id);
        mysqli_stmt_execute($stmt_cat);
        $result = mysqli_stmt_get_result($stmt_cat);
        while ($row = mysqli_fetch_assoc($result)) { $categories[] = $row; }
        mysqli_stmt_close($stmt_cat);
    }
    
    // Fetch study goals for the current tenant
    $sql_goals = "SELECT id, title, description, category, finish_by, is_completed, created_at FROM study_goals WHERE tenant_id = ? ORDER BY created_at DESC";
    if ($stmt_goals = mysqli_prepare($link, $sql_goals)) {
        mysqli_stmt_bind_param($stmt_goals, "i", $tenant_id);
        mysqli_stmt_execute($stmt_goals);
        $result = mysqli_stmt_get_result($stmt_goals);
        while ($row = mysqli_fetch_assoc($result)) { $study_goals[] = $row; }
        mysqli_stmt_close($stmt_goals);
    }
}

// --- RENDER PAGE ---
require_once(BASE_PATH . '/partials/header.php');
?>
<style>
    .goal-description {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease-out, margin 0.3s ease-out;
    }
    .goal-header.active-goal + .goal-description {
        max-height: 800px; /* Adjusted for potentially longer AI content */
        margin-top: 1rem;
        transition: max-height 0.5s ease-in, margin 0.5s ease-in;
        overflow-y: auto;
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
                            <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                            <button type="submit" name="delete_goal" class="text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                        </form>
                    </div>
                </div>
                <div class="goal-description">
                     <div class="p-6 rounded-lg bg-gradient-to-r from-purple-500 to-indigo-500 text-white">
                        <p class="whitespace-pre-wrap"><?php echo htmlspecialchars($goal['description'] ?: 'No description provided.'); ?></p>
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
        <form method="post">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Goal Title</label>
                <input type="text" name="title" id="add_title" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-4">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="flex space-x-2">
                        <button type="button" onclick="makeGoalSmart('add')" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200 font-semibold transition-colors">
                            ✨ Make SMART
                        </button>
                        <button type="button" onclick="generateMilestones('add')" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 font-semibold transition-colors">
                            📍 Milestones
                        </button>
                    </div>
                </div>
                <textarea name="description" id="add_description" rows="5" class="block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="add_category" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Uncategorized</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Finish By</label>
                    <input type="date" name="finish_by" id="add_finish_by" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('add-goal-modal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="add_goal" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Add Goal</button>
            </div>
        </form>
    </div>
</div>

<div id="edit-goal-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Edit Study Goal</h3>
        <form method="post">
            <input type="hidden" name="goal_id" id="edit_goal_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Goal Title</label>
                <input type="text" name="title" id="edit_title" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-4">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="flex space-x-2">
                        <button type="button" onclick="makeGoalSmart('edit')" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200 font-semibold transition-colors">
                            ✨ Make SMART
                        </button>
                        <button type="button" onclick="generateMilestones('edit')" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 font-semibold transition-colors">
                            📍 Milestones
                        </button>
                    </div>
                </div>
                <textarea name="description" id="edit_description" rows="5" class="block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="edit_category" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Uncategorized</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Finish By</label>
                    <input type="date" name="finish_by" id="edit_finish_by" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('edit-goal-modal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="edit_goal" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Save Changes</button>
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
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" name="delete_category" class="text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <form method="post" id="add-category-form" class="flex space-x-2">
            <input type="text" name="name" class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" placeholder="New category name..." required>
            <button type="submit" name="add_category" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">Add</button>
        </form>
        <form method="post" id="edit-category-form" class="hidden space-x-2">
             <input type="hidden" name="category_id" id="edit_category_id">
            <input type="text" name="name" id="edit_category_name" class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            <button type="submit" name="update_category" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Save</button>
            <button type="button" onclick="hideEditCategory()" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
        </form>
        <div class="text-right mt-6">
            <button type="button" onclick="closeModal('category-manager-modal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Close</button>
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
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_category_name').value = name;
    }

    function hideEditCategory() {
        document.getElementById('add-category-form').classList.remove('hidden');
        document.getElementById('edit-category-form').classList.add('hidden');
    }
    
    // --- INLINE VIEW SCRIPT ---
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

    // --- AI SMART GOAL REFINEMENT SCRIPT ---
    async function makeGoalSmart(mode) {
        const titleInput = mode === 'add' ? document.getElementById('add_title') : document.getElementById('edit_title');
        const descInput = mode === 'add' ? document.getElementById('add_description') : document.getElementById('edit_description');
        
        const title = titleInput.value.trim();
        const desc = descInput.value.trim();

        if (!title) {
            alert('Please enter a Goal Title first so the AI knows what to refine.');
            titleInput.focus();
            return;
        }

        const originalDesc = descInput.value;
        descInput.value = "AI is refining your goal into a SMART format... Please wait.";
        descInput.disabled = true;

        try {
            const prompt = `You are an expert productivity coach. Rewrite the following user goal into a SMART goal (Specific, Measurable, Achievable, Relevant, Time-bound). 
            Original Title: "${title}"
            Original Description: "${desc}"
            
            Respond in this EXACT format:
            TITLE: [Your revised concise title]
            DESCRIPTION: [Your detailed SMART description]`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/gemini-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            const reply = data.reply;
            const titleMatch = reply.match(/TITLE:\s*(.*)/i);
            const descMatch = reply.match(/DESCRIPTION:\s*([\s\S]*)/i);

            if (titleMatch && titleMatch[1]) titleInput.value = titleMatch[1].trim();
            if (descMatch && descMatch[1]) descInput.value = descMatch[1].trim();
            else descInput.value = reply; // Fallback if format isn't strictly followed

        } catch (error) {
            console.error('AI SMART Goal Error:', error);
            alert('Failed to refine goal: ' + error.message);
            descInput.value = originalDesc;
        } finally {
            descInput.disabled = false;
        }
    }

    // --- AI MILESTONE GENERATOR SCRIPT ---
    async function generateMilestones(mode) {
        const titleInput = mode === 'add' ? document.getElementById('add_title') : document.getElementById('edit_title');
        const descInput = mode === 'add' ? document.getElementById('add_description') : document.getElementById('edit_description');
        const dateInput = mode === 'add' ? document.getElementById('add_finish_by') : document.getElementById('edit_finish_by');
        
        const title = titleInput.value.trim();
        const desc = descInput.value.trim();
        const deadline = dateInput.value;

        if (!title || !deadline) {
            alert('Please enter both a Goal Title and a Finish By date so the AI can generate milestones.');
            return;
        }

        const originalDesc = descInput.value;
        descInput.value = "AI is generating milestones based on your deadline... Please wait.";
        descInput.disabled = true;

        try {
            const today = new Date().toISOString().split('T')[0];
            const prompt = `You are a productivity expert helping a student. Based on the study goal and deadline below, generate a concise, sequential timeline of milestones to help keep the student on track.
            Goal Title: "${title}"
            Description: "${desc}"
            Deadline: "${deadline}" (Today is ${today})
            
            Only return the bulleted list of milestones with suggested dates/weeks. Keep it brief and actionable.`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/gemini-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            const newText = (originalDesc ? originalDesc + "\n\n" : "") + "--- Suggested Milestones ---\n" + data.reply;
            descInput.value = newText;

        } catch (error) {
            console.error('AI Milestone Error:', error);
            alert('Failed to generate milestones: ' + error.message);
            descInput.value = originalDesc;
        } finally {
            descInput.disabled = false;
        }
    }
</script>

<?php
// --- CLEANUP ---
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>