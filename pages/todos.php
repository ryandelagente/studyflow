<?php
// File: /pages/todos.php
// Purpose: Display and manage the To-do list with a new description field.

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- Initialization ---
$error_message   = '';
$success_message = '';
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
$tasks     = [];   // always initialised so goto render never hits an undefined variable

// --- Database Connection Check ---
if (!$link) {
    $error_message = "Database connection failed: " . mysqli_connect_error();
}

// --- CRUD Logic ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify()) { $error_message = "Security token mismatch. Please try again."; goto render; }

    // AI BULK CREATE TASKS
    if (isset($_POST['ai_add_tasks'])) {
        $tasks_json = $_POST['selected_tasks'] ?? '';
        $tasks = json_decode($tasks_json, true);
        if ($tasks && is_array($tasks)) {
            $sql = "INSERT INTO tasks (user_id, tenant_id, title, priority) VALUES (?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                foreach ($tasks as $t) {
                    $title    = trim($t['title'] ?? '');
                    $priority = strtoupper(trim($t['priority'] ?? 'MEDIUM'));
                    if (!in_array($priority, ['HIGH', 'MEDIUM', 'LOW'])) $priority = 'MEDIUM';
                    if (!empty($title)) {
                        mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $title, $priority);
                        mysqli_stmt_execute($stmt);
                    }
                }
                mysqli_stmt_close($stmt);
                flash_set('success', 'AI-generated tasks added successfully.');
                header("Location: todos.php"); exit();
            }
        }
        $error_message = "Failed to save AI-generated tasks.";
    }

    // CREATE: Add a new task
    if (isset($_POST['add_task'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']); // New field
        $priority = trim($_POST['priority']);
        $finish_by = !empty($_POST['finish_by']) ? trim($_POST['finish_by']) : null;

        $sql = "INSERT INTO tasks (user_id, tenant_id, title, description, priority, finish_by) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iissss", $user_id, $tenant_id, $title, $description, $priority, $finish_by);
            if (mysqli_stmt_execute($stmt)) {
                flash_set('success', 'Task added successfully.');
                header("Location: todos.php"); exit();
            } else { $error_message = "Error creating task."; }
        }
    }

    // UPDATE: Modify an existing task
    if (isset($_POST['update_task'])) {
        $task_id = trim($_POST['task_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']); // New field
        $priority = trim($_POST['priority']);
        $finish_by = !empty($_POST['finish_by']) ? trim($_POST['finish_by']) : null;

        $sql = "UPDATE tasks SET title = ?, description = ?, priority = ?, finish_by = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssiii", $title, $description, $priority, $finish_by, $task_id, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                flash_set('success', 'Task updated successfully.');
                header("Location: todos.php"); exit();
            } else { $error_message = "Error updating task."; }
        }
    }
    
    // Other POST actions (toggle, delete) remain the same...
    if (isset($_POST['toggle_task'])) {
        $task_id = trim($_POST['task_id']);
        $is_completed = isset($_POST['is_completed']) ? 1 : 0;
        $sql = "UPDATE tasks SET is_completed = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "iiii", $is_completed, $task_id, $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            header("Location: todos.php");
            exit;
        }
    }

    if (isset($_POST['delete_task'])) {
        $task_id = trim($_POST['task_id']);
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $task_id, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                flash_set('success', 'Task deleted.');
                header("Location: todos.php"); exit();
            } else { $error_message = "Error deleting task."; }
        }
    }
}

$success_message = $success_message ?: flash_get('success');
$error_message   = $error_message   ?: flash_get('error');

render:
// READ: Fetch all tasks — runs even after a CSRF failure so the list is always shown
if ($link) {
    $sql = "SELECT id, title, description, priority, finish_by, is_completed FROM tasks WHERE user_id = ? AND tenant_id = ? ORDER BY created_at DESC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $tasks[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>
<style>
    .task-description {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease-out;
        padding: 0 1rem;
    }
    .task-header.active-task + .task-description {
        max-height: 500px; /* Adjust as needed */
        padding: 1rem 1rem 1rem 3.5rem; /* Indent the description */
        transition: max-height 0.5s ease-in, padding 0.5s ease-in;
    }
    .task-header.active-task {
        background-color: #f5f3ff; /* light purple background from screenshot */
    }
</style>

<main class="flex-1 p-6 bg-gray-100">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">To-dos / <span class="text-gray-500">Manage your tasks</span></h1>
        <div class="flex gap-2">
            <button onclick="openModal('aiTodosModal')" class="bg-green-100 text-green-800 px-6 py-2 rounded-lg hover:bg-green-200 font-semibold"><i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>AI Generate</button>
            <button onclick="openModal('addTodoModal')" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Add To-do</button>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md">
        <div class="space-y-0"> <?php if (empty($tasks) && empty($error_message)): ?>
                <p class="text-center text-gray-500 p-6">You have no tasks yet. Click "Add To-do" to get started!</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item border-b border-gray-200 last:border-b-0">
                        <div class="task-header flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50" onclick="toggleTaskView(this)">
                            <div class="flex items-center">
                                <form method="POST" class="mr-4" onclick="event.stopPropagation();">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="checkbox" name="is_completed" <?php echo $task['is_completed'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <input type="hidden" name="toggle_task" value="1">
                                </form>
                                <div>
                                    <p class="font-medium text-gray-800 <?php echo $task['is_completed'] ? 'line-through text-gray-400' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Finish by: <?php echo htmlspecialchars($task['finish_by'] ? date('d M Y', strtotime($task['finish_by'])) : 'N/A'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2" onclick="event.stopPropagation();">
                                <?php
                                    $priority_class = 'bg-red-100 text-red-800';
                                    if (strtoupper($task['priority']) == 'MEDIUM') $priority_class = 'bg-yellow-100 text-yellow-800';
                                    if (strtoupper($task['priority']) == 'LOW') $priority_class = 'bg-green-100 text-green-800';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $priority_class; ?>">
                                    <?php echo htmlspecialchars($task['priority']); ?>
                                </span>
                                <button onclick='openEditModal(<?php echo htmlspecialchars(json_encode($task)); ?>)' class="text-gray-400 hover:text-blue-600 p-1"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                                <form method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" name="delete_task" class="text-gray-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="task-description bg-gray-50 text-gray-600">
                            <p class="whitespace-pre-wrap"><?php echo htmlspecialchars($task['description'] ?: 'No description provided.'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<div id="addTodoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6">Add New To-do</h2>
        <form method="POST"><?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium">Task*</label>
                <input type="text" name="title" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="4" class="mt-1 block w-full border rounded-md p-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Priority*</label>
                <select name="priority" class="mt-1 block w-full border rounded-md p-2" required>
                    <option value="LOW">Low</option>
                    <option value="MEDIUM" selected>Medium</option>
                    <option value="HIGH">High</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Deadline</label>
                <input type="date" name="finish_by" class="mt-1 block w-full border rounded-md p-2">
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('addTodoModal')" class="bg-gray-200 px-6 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="add_task" class="bg-purple-600 text-white px-6 py-2 rounded-lg">Add Task</button>
            </div>
        </form>
    </div>
</div>

<div id="editTodoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6">Edit Task</h2>
        <form method="POST"><?php echo csrf_field(); ?>
            <input type="hidden" name="task_id" id="edit_task_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Task*</label>
                <input type="text" name="title" id="edit_title" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" id="edit_description" rows="4" class="mt-1 block w-full border rounded-md p-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Priority*</label>
                <select name="priority" id="edit_priority" class="mt-1 block w-full border rounded-md p-2" required>
                    <option value="LOW">Low</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="HIGH">High</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Deadline</label>
                <input type="date" name="finish_by" id="edit_finish_by" class="mt-1 block w-full border rounded-md p-2">
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('editTodoModal')" class="bg-gray-200 px-6 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="update_task" class="bg-purple-600 text-white px-6 py-2 rounded-lg">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Modal Handling ---
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

    function openEditModal(task) {
        document.getElementById('edit_task_id').value = task.id;
        document.getElementById('edit_title').value = task.title;
        document.getElementById('edit_description').value = task.description;
        document.getElementById('edit_priority').value = task.priority;
        document.getElementById('edit_finish_by').value = task.finish_by ? task.finish_by.split(' ')[0] : '';
        openModal('editTodoModal');
    }
    
    // --- INLINE VIEW SCRIPT ---
    let activeTask = null;
    function toggleTaskView(taskHeader) {
        // If there's an active task and it's not the one we just clicked, close it.
        if (activeTask && activeTask !== taskHeader) {
            activeTask.classList.remove('active-task');
        }

        // Toggle the current task
        taskHeader.classList.toggle('active-task');

        // Update the activeTask variable
        if (taskHeader.classList.contains('active-task')) {
            activeTask = taskHeader;
        } else {
            activeTask = null;
        }
    }

    // --- AI Generate Tasks ---
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('ai-todos-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const context = document.getElementById('ai-todos-context').value.trim();
            const count   = document.getElementById('ai-todos-count').value;
            const btn     = document.getElementById('ai-todos-btn');
            const errDiv  = document.getElementById('ai-todos-error');
            errDiv.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Generating...';
            document.getElementById('ai-todos-preview').classList.add('hidden');

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/ai-assist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
                    body: JSON.stringify({ feature: 'todos_generate', context, count: parseInt(count) })
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                const tasks = JSON.parse(data.result);
                if (!Array.isArray(tasks) || tasks.length === 0) throw new Error('No tasks were generated.');

                const container = document.getElementById('ai-todos-list');
                container.innerHTML = '';
                const priorityColors = { HIGH: 'bg-red-100 text-red-800', MEDIUM: 'bg-yellow-100 text-yellow-800', LOW: 'bg-green-100 text-green-800' };
                tasks.forEach((t, i) => {
                    const p = (t.priority || 'MEDIUM').toUpperCase();
                    const pClass = priorityColors[p] || priorityColors.MEDIUM;
                    container.innerHTML += `<label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="ai_tasks[]" value="${i}" checked class="w-4 h-4">
                        <span class="flex-1 text-sm">${t.title}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full ${pClass}">${p}</span>
                    </label>`;
                });
                document.getElementById('ai-todos-data').value = JSON.stringify(tasks);
                document.getElementById('ai-todos-preview').classList.remove('hidden');
            } catch (err) {
                errDiv.textContent = 'Error: ' + err.message;
                errDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Generate';
            }
        });

        document.getElementById('ai-todos-save-form').addEventListener('submit', function() {
            const checked  = document.querySelectorAll('input[name="ai_tasks[]"]:checked');
            const allTasks = JSON.parse(document.getElementById('ai-todos-data').value);
            const selected = Array.from(checked).map(cb => allTasks[parseInt(cb.value)]);
            document.getElementById('ai-todos-selected').value = JSON.stringify(selected);
        });
    });
</script>

<!-- AI Generate Tasks Modal -->
<div id="aiTodosModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-green-600"></i> AI Generate Tasks</h2>
        <form id="ai-todos-form">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">What do you need to accomplish?</label>
                <textarea id="ai-todos-context" rows="3" class="w-full border rounded-md p-2" placeholder="e.g. Prepare for my calculus exam next week, Write a 10-page history essay..." required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Tasks</label>
                <select id="ai-todos-count" class="w-full border rounded-md p-2">
                    <option value="5" selected>5 tasks</option>
                    <option value="8">8 tasks</option>
                    <option value="10">10 tasks</option>
                </select>
            </div>
            <div id="ai-todos-error" class="hidden bg-red-100 text-red-700 px-3 py-2 rounded mb-4 text-sm"></div>
            <div class="flex justify-end gap-2 mb-4">
                <button type="button" onclick="closeModal('aiTodosModal')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" id="ai-todos-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Generate</button>
            </div>
        </form>
        <div id="ai-todos-preview" class="hidden">
            <h3 class="font-semibold text-gray-700 mb-2">Select tasks to add</h3>
            <form id="ai-todos-save-form" method="POST">
                <?php echo csrf_field(); ?>
                <div id="ai-todos-list" class="space-y-2 mb-4 max-h-64 overflow-y-auto"></div>
                <input type="hidden" name="selected_tasks" id="ai-todos-selected">
                <input type="hidden" id="ai-todos-data" value="[]">
                <div class="flex justify-end">
                    <button type="submit" name="ai_add_tasks" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Add Selected Tasks</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) {
    mysqli_close($link);
}
?>