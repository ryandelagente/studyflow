<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/todos.php
// Purpose: Display and manage the To-do list with AI features.

require_once(__DIR__ . '/../config.php');

// --- Initialization ---
$error_message = '';
$success_message = '';
$user_id = $_SESSION["id"]; 
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session

// --- Database Connection Check ---
if (!$link) {
    $error_message = "Database connection failed: " . mysqli_connect_error();
}

// --- CRUD Logic ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CREATE: Add a new task
    if (isset($_POST['add_task'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $priority = trim($_POST['priority']);
        $finish_by = !empty($_POST['finish_by']) ? trim($_POST['finish_by']) : null;
        $estimated_time = trim($_POST['estimated_time'] ?? '');

        $sql = "INSERT INTO tasks (user_id, tenant_id, title, description, priority, finish_by, estimated_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iisssss", $user_id, $tenant_id, $title, $description, $priority, $finish_by, $estimated_time);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: todos.php?success=1");
                exit();
            } else { $error_message = "Error creating task."; }
        }
    }

    // UPDATE: Modify an existing task
    if (isset($_POST['update_task'])) {
        $task_id = trim($_POST['task_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $priority = trim($_POST['priority']);
        $finish_by = !empty($_POST['finish_by']) ? trim($_POST['finish_by']) : null;
        $estimated_time = trim($_POST['estimated_time'] ?? '');

        $sql = "UPDATE tasks SET title = ?, description = ?, priority = ?, finish_by = ?, estimated_time = ? WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssii", $title, $description, $priority, $finish_by, $estimated_time, $task_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: todos.php?success=2");
                exit();
            } else { $error_message = "Error updating task."; }
        }
    }
    
    // TOGGLE
    if (isset($_POST['toggle_task'])) {
        $task_id = trim($_POST['task_id']);
        $is_completed = isset($_POST['is_completed']) ? 1 : 0;
        $sql = "UPDATE tasks SET is_completed = ? WHERE id = ? AND tenant_id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "iii", $is_completed, $task_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            header("Location: todos.php");
            exit;
        }
    }

    // DELETE
    if (isset($_POST['delete_task'])) {
        $task_id = trim($_POST['task_id']);
        $sql = "DELETE FROM tasks WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $task_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: todos.php?success=3");
                exit();
            } else { $error_message = "Error deleting task."; }
        }
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1: $success_message = "Task added successfully."; break;
        case 2: $success_message = "Task updated successfully."; break;
        case 3: $success_message = "Task deleted successfully."; break;
    }
}


// READ: Fetch all tasks for the current tenant
$tasks = [];
if ($link) {
    // Note: ensure estimated_time is selected!
    $sql = "SELECT id, title, description, priority, finish_by, estimated_time, is_completed FROM tasks WHERE tenant_id = ? ORDER BY created_at DESC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $tenant_id);
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
        overflow-y: auto;
    }
    .task-header.active-task {
        background-color: #f5f3ff; /* light purple background from screenshot */
    }
    .priority-highlight {
        animation: highlightPulse 2s ease-out;
    }
    @keyframes highlightPulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); background-color: #ecfdf5; }
        70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); background-color: #ffffff; }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>

<main class="flex-1 p-6 bg-gray-100">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">To-dos / <span class="text-gray-500">Manage your tasks</span></h1>
        <button onclick="openModal('addTodoModal')" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">Add To-do</button>
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
                        <div class="task-header flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors" onclick="toggleTaskView(this)">
                            <div class="flex items-center">
                                <form method="POST" class="mr-4" onclick="event.stopPropagation();">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="checkbox" name="is_completed" <?php echo $task['is_completed'] ? 'checked' : ''; ?> onchange="this.form.submit()" class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                    <input type="hidden" name="toggle_task" value="1">
                                </form>
                                <div>
                                    <p class="font-medium text-gray-800 <?php echo $task['is_completed'] ? 'line-through text-gray-400' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </p>
                                    <div class="text-sm text-gray-500 mt-1 flex items-center space-x-4">
                                        <?php if ($task['finish_by']): ?>
                                            <span class="flex items-center"><i data-lucide="calendar" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars(date('d M Y', strtotime($task['finish_by']))); ?></span>
                                        <?php else: ?>
                                            <span class="flex items-center"><i data-lucide="calendar" class="w-3 h-3 mr-1"></i> No Deadline</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($task['estimated_time'])): ?>
                                            <span class="flex items-center text-blue-600"><i data-lucide="clock" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($task['estimated_time']); ?></span>
                                        <?php endif; ?>
                                    </div>
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
                                <button onclick='openEditModal(<?php echo htmlspecialchars(json_encode($task)); ?>)' class="text-gray-400 hover:text-blue-600 p-1 transition-colors"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                                <form method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" name="delete_task" class="text-gray-400 hover:text-red-600 p-1 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
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
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Task Title*</label>
                <input type="text" name="title" id="add_title" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-4">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <button type="button" onclick="generateAIBreakdown('add')" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200 font-semibold transition-colors">
                        ✨ Ask AI to Break Down
                    </button>
                </div>
                <textarea name="description" id="add_description" rows="3" class="block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Task details or sub-tasks..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deadline</label>
                    <input type="date" name="finish_by" id="add_finish_by" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Time Needed</label>
                        <button type="button" onclick="estimateTime('add', event)" class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                            ⏱️ Estimate
                        </button>
                    </div>
                    <input type="text" name="estimated_time" id="add_estimated_time" class="mt-0 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow duration-300" placeholder="e.g., 2 hours">
                </div>
            </div>
            <div class="mb-6">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">Priority*</label>
                    <button type="button" onclick="autoPrioritize('add', event)" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 font-semibold transition-colors">
                        🤖 Auto-Prioritize
                    </button>
                </div>
                <select name="priority" id="add_priority" class="block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow duration-300" required>
                    <option value="LOW">Low</option>
                    <option value="MEDIUM" selected>Medium</option>
                    <option value="HIGH">High</option>
                </select>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('addTodoModal')" class="bg-gray-200 px-6 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="add_task" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">Add Task</button>
            </div>
        </form>
    </div>
</div>

<div id="editTodoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6">Edit Task</h2>
        <form method="POST">
            <input type="hidden" name="task_id" id="edit_task_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Task Title*</label>
                <input type="text" name="title" id="edit_title" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-4">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <button type="button" onclick="generateAIBreakdown('edit')" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200 font-semibold transition-colors">
                        ✨ Ask AI to Break Down
                    </button>
                </div>
                <textarea name="description" id="edit_description" rows="3" class="block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deadline</label>
                    <input type="date" name="finish_by" id="edit_finish_by" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Time Needed</label>
                        <button type="button" onclick="estimateTime('edit', event)" class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                            ⏱️ Estimate
                        </button>
                    </div>
                    <input type="text" name="estimated_time" id="edit_estimated_time" class="mt-0 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow duration-300" placeholder="e.g., 2 hours">
                </div>
            </div>
            <div class="mb-6">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">Priority*</label>
                    <button type="button" onclick="autoPrioritize('edit', event)" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 font-semibold transition-colors">
                        🤖 Auto-Prioritize
                    </button>
                </div>
                <select name="priority" id="edit_priority" class="block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow duration-300" required>
                    <option value="LOW">Low</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="HIGH">High</option>
                </select>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('editTodoModal')" class="bg-gray-200 px-6 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="update_task" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">Save Changes</button>
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
        document.getElementById('edit_estimated_time').value = task.estimated_time || '';
        document.getElementById('edit_finish_by').value = task.finish_by ? task.finish_by.split(' ')[0] : '';
        openModal('editTodoModal');
    }
    
    // --- INLINE VIEW SCRIPT ---
    let activeTask = null;
    function toggleTaskView(taskHeader) {
        if (activeTask && activeTask !== taskHeader) {
            activeTask.classList.remove('active-task');
        }
        taskHeader.classList.toggle('active-task');
        if (taskHeader.classList.contains('active-task')) {
            activeTask = taskHeader;
        } else {
            activeTask = null;
        }
    }

    // --- AI TASK BREAKDOWN SCRIPT ---
    async function generateAIBreakdown(mode) {
        const titleInput = mode === 'add' ? document.getElementById('add_title') : document.getElementById('edit_title');
        const descInput = mode === 'add' ? document.getElementById('add_description') : document.getElementById('edit_description');
        
        const title = titleInput.value.trim();
        if (!title) {
            alert('Please enter a Task Title first so the AI knows what to break down.');
            titleInput.focus();
            return;
        }

        const originalText = descInput.value;
        descInput.value = "AI is breaking down the task... Please wait.";
        descInput.disabled = true;

        try {
            const prompt = `Please break down this task into a concise, step-by-step bulleted list of actionable sub-tasks. Only return the list, nothing else. The task is: "${title}"`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            const newText = (originalText ? originalText + "\n\n" : "") + "--- AI Breakdown ---\n" + data.reply;
            descInput.value = newText;

        } catch (error) {
            console.error('AI Breakdown Error:', error);
            alert('Failed to generate breakdown: ' + error.message);
            descInput.value = originalText;
        } finally {
            descInput.disabled = false;
        }
    }

    // --- AI SMART PRIORITIZATION SCRIPT ---
    async function autoPrioritize(mode, event) {
        const titleInput = mode === 'add' ? document.getElementById('add_title') : document.getElementById('edit_title');
        const descInput = mode === 'add' ? document.getElementById('add_description') : document.getElementById('edit_description');
        const dateInput = mode === 'add' ? document.getElementById('add_finish_by') : document.getElementById('edit_finish_by');
        const prioritySelect = mode === 'add' ? document.getElementById('add_priority') : document.getElementById('edit_priority');
        
        const btn = event.currentTarget;

        const title = titleInput.value.trim();
        const desc = descInput.value.trim();
        const deadline = dateInput.value;

        if (!title) {
            alert('Please enter a Task Title first so the AI can evaluate the priority.');
            titleInput.focus();
            return;
        }

        const originalBtnText = btn.innerHTML;
        btn.innerHTML = "⏳ Thinking...";
        btn.disabled = true;

        try {
            const today = new Date().toISOString().split('T')[0];
            const prompt = `You are a productivity assistant. Determine the priority of this task based on urgency and complexity.
            Task Title: "${title}"
            Task Description: "${desc}"
            Deadline: "${deadline ? deadline : 'No deadline set'}" (Today is ${today})
            
            Respond ONLY with one of the following exact words: HIGH, MEDIUM, or LOW. Do not add any punctuation, markdown, or extra text.`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            const aiPriority = data.reply.trim().toUpperCase().replace(/[^A-Z]/g, '');
            
            if (['HIGH', 'MEDIUM', 'LOW'].includes(aiPriority)) {
                prioritySelect.value = aiPriority;
                
                prioritySelect.classList.remove('border-gray-300');
                prioritySelect.classList.add('priority-highlight');
                
                setTimeout(() => {
                    prioritySelect.classList.remove('priority-highlight');
                    prioritySelect.classList.add('border-gray-300');
                }, 2000);

            } else {
                console.warn('AI returned unexpected priority:', data.reply);
                alert('AI could not determine a clear priority. Please set it manually.');
            }

        } catch (error) {
            console.error('AI Prioritization Error:', error);
            alert('Failed to determine priority: ' + error.message);
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    }

    // --- AI TIME ESTIMATION SCRIPT ---
    async function estimateTime(mode, event) {
        const titleInput = mode === 'add' ? document.getElementById('add_title') : document.getElementById('edit_title');
        const descInput = mode === 'add' ? document.getElementById('add_description') : document.getElementById('edit_description');
        const timeInput = mode === 'add' ? document.getElementById('add_estimated_time') : document.getElementById('edit_estimated_time');
        
        const btn = event.currentTarget;
        const title = titleInput.value.trim();
        const desc = descInput.value.trim();

        if (!title) {
            alert('Please enter a Task Title first so the AI can estimate the time.');
            titleInput.focus();
            return;
        }

        const originalBtnText = btn.innerHTML;
        btn.innerHTML = "⏳ Thinking...";
        btn.disabled = true;

        try {
            const prompt = `You are a productivity assistant. Estimate how long it will take to complete this task.
            Task Title: "${title}"
            Task Description: "${desc}"
            
            Respond ONLY with a concise time estimate (e.g., "30 mins", "2 hours", "1-2 days"). Do not add any punctuation, markdown, or extra text. Keep it extremely brief.`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            timeInput.value = data.reply.trim();
            
            // Visual highlight
            timeInput.classList.remove('border-gray-300');
            timeInput.classList.add('priority-highlight');
            
            setTimeout(() => {
                timeInput.classList.remove('priority-highlight');
                timeInput.classList.add('border-gray-300');
            }, 2000);

        } catch (error) {
            console.error('AI Estimation Error:', error);
            alert('Failed to estimate time: ' + error.message);
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    }
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) {
    mysqli_close($link);
}
?>