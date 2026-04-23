<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// /pages/assignments.php - Updated with Auto-Outlining & Reverse Scheduling

require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session
$db_error = null;
$success_message = null;
$assignments = [];
$study_goals = [];
$contacts = [];

// Determine view based on URL parameter
$action = $_GET['action'] ?? 'list'; // 'list', 'create', 'edit'
$edit_id = $_GET['id'] ?? null;
$assignment_to_edit = null;

if (!isset($link) || !$link) {
    $db_error = "Database connection failed.";
} else {

    // --- AJAX HANDLER FOR AI REVERSE SCHEDULING ---
    // This intercepts the request from JS and adds the AI milestones directly to the To-do list (tasks table)
    if (isset($_POST['ajax_add_tasks'])) {
        header('Content-Type: application/json');
        $tasks_json = json_decode($_POST['tasks'], true);
        $assignment_title = trim($_POST['assignment_title'] ?? 'Assignment');
        
        if (is_array($tasks_json)) {
            $added = 0;
            foreach ($tasks_json as $t) {
                // Prefix task title with assignment title for context
                $t_title = $assignment_title . ': ' . ($t['title'] ?? 'Milestone');
                $t_date = $t['finish_by'] ?? null;
                
                $sql = "INSERT INTO tasks (user_id, tenant_id, title, priority, finish_by) VALUES (?, ?, ?, 'MEDIUM', ?)";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $t_title, $t_date);
                    if (mysqli_stmt_execute($stmt)) {
                        $added++;
                    }
                }
            }
            echo json_encode(['success' => true, 'added' => $added]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON returned by AI']);
        }
        exit;
    }

    // --- CRUD LOGIC ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax_add_tasks'])) {
        // CREATE
        if (isset($_POST['add_assignment'])) {
            $title = trim($_POST['title']);
            $related_goal_id = !empty($_POST['related_goal_id']) ? intval($_POST['related_goal_id']) : null;
            $status = trim($_POST['status']);
            $summary = trim($_POST['summary']);
            $team_members = isset($_POST['team_members']) ? implode(', ', $_POST['team_members']) : '';
            $start_date = !empty($_POST['start_date']) ? trim($_POST['start_date']) : null;
            $end_date = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
            $description = trim($_POST['description']);
            $course = $title; 

            $sql = "INSERT INTO assignments (user_id, tenant_id, title, course, description, start_date, end_date, related_goal_id, team_members, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iisssssiss", $user_id, $tenant_id, $title, $course, $description, $start_date, $end_date, $related_goal_id, $team_members, $status);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: assignments.php?success=1");
                    exit();
                } else { $db_error = "Error creating assignment: " . mysqli_stmt_error($stmt); }
            }
        }
        // UPDATE
        if (isset($_POST['update_assignment'])) {
            $assignment_id = $_POST['assignment_id'];
            $title = trim($_POST['title']);
            $related_goal_id = !empty($_POST['related_goal_id']) ? intval($_POST['related_goal_id']) : null;
            $status = trim($_POST['status']);
            $summary = trim($_POST['summary']);
            $team_members = isset($_POST['team_members']) ? implode(', ', $_POST['team_members']) : '';
            $start_date = !empty($_POST['start_date']) ? trim($_POST['start_date']) : null;
            $end_date = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
            $description = trim($_POST['description']);
            $course = $title;

            $sql = "UPDATE assignments SET title = ?, course = ?, description = ?, start_date = ?, end_date = ?, related_goal_id = ?, team_members = ?, status = ? WHERE id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssissii", $title, $course, $description, $start_date, $end_date, $related_goal_id, $team_members, $status, $assignment_id, $tenant_id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: assignments.php?success=2");
                    exit();
                } else { $db_error = "Error updating assignment: " . mysqli_stmt_error($stmt); }
            }
        }
        // DELETE
        if (isset($_POST['delete_assignment'])) {
            $assignment_id = $_POST['assignment_id'];
            $sql = "DELETE FROM assignments WHERE id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $tenant_id);
                mysqli_stmt_execute($stmt);
                header("Location: assignments.php?success=3");
                exit();
            }
        }
    }

    // --- READ DATA ---
    if ($action == 'list') {
        $sql = "SELECT * FROM assignments WHERE tenant_id = ? ORDER BY end_date ASC";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $assignments[] = $row; }
        }
    }
    
    // If showing form, fetch data for dropdowns
    if ($action == 'create' || $action == 'edit') {
        // Fetch study goals for the current tenant
        $sql_goals = "SELECT id, title FROM study_goals WHERE tenant_id = ? AND is_completed = 0 ORDER BY title ASC";
        if ($stmt = mysqli_prepare($link, $sql_goals)) {
            mysqli_stmt_bind_param($stmt, "i", $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $study_goals[] = $row; }
        }

        // Fetch contacts for the current tenant
        $sql_contacts = "SELECT id, first_name, last_name FROM contacts WHERE tenant_id = ? ORDER BY first_name ASC";
        if ($stmt = mysqli_prepare($link, $sql_contacts)) {
            mysqli_stmt_bind_param($stmt, "i", $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $contacts[] = $row; }
        }
        
        // If editing, fetch the specific assignment's details
        if ($action == 'edit' && $edit_id) {
            $sql = "SELECT * FROM assignments WHERE id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $edit_id, $tenant_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $assignment_to_edit = mysqli_fetch_assoc($result);
            }
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>
<script src="https://cdn.tiny.cloud/1/0mgprpbs33v7zrkjsne3egwjvfuk9sd268xim5mrj8iz8npk/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea#project_description',
    plugins: 'lists link image table code help wordcount',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | help'
  });
</script>

<main class="flex-1 p-8 overflow-y-auto">
    <?php if ($action === 'create' || $action === 'edit'): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">
                <a href="assignments.php" class="text-gray-400 hover:text-purple-600">Assignments</a> / 
                <?php echo $action === 'create' ? 'New Assignment/Study plan' : 'Edit Assignment'; ?>
            </h2>
            <p class="text-gray-500">
                <?php echo $action === 'create' ? 'Create a new assignment' : 'Update assignment details'; ?>
            </p>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
            <form method="POST">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="assignment_id" value="<?php echo $assignment_to_edit['id']; ?>">
                <?php endif; ?>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">TITLE*</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($assignment_to_edit['title'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RELATED GOAL</label>
                            <select name="related_goal_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="">None</option>
                                <?php foreach ($study_goals as $goal): ?>
                                    <option value="<?php echo $goal['id']; ?>" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['related_goal_id'] == $goal['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($goal['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">STATUS*</label>
                            <select name="status" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="PENDING" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['status'] == 'PENDING') ? 'selected' : ''; ?>>Pending</option>
                                <option value="STARTED" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['status'] == 'STARTED') ? 'selected' : ''; ?>>Started</option>
                                <option value="FINISHED" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['status'] == 'FINISHED') ? 'selected' : ''; ?>>Finished</option>
                            </select>
                        </div>
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-gray-700">ASSIGNMENT SUMMARY</label>
                         <textarea name="summary" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"><?php echo htmlspecialchars($assignment_to_edit['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SELECT TEAM / GROUP MEMBERS</label>
                        <select name="team_members[]" multiple class="mt-1 block w-full border border-gray-300 rounded-md p-2 h-32 focus:ring-purple-500 focus:border-purple-500">
                            <?php 
                                $selected_members = isset($assignment_to_edit) ? explode(', ', $assignment_to_edit['team_members']) : [];
                                foreach ($contacts as $contact): 
                                    $contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                            ?>
                                <option value="<?php echo htmlspecialchars($contact_name); ?>" <?php echo in_array($contact_name, $selected_members) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($contact_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">START DATE</label>
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($assignment_to_edit['start_date'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">END DATE</label>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($assignment_to_edit['end_date'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-medium text-gray-700">PROJECT DESCRIPTION (Prompt)</label>
                            <div class="flex space-x-2">
                                <button type="button" onclick="generateAIOutline()" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200 font-semibold transition-colors">
                                    ✨ AI Outline
                                </button>
                                <button type="button" onclick="generateReverseSchedule()" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 font-semibold transition-colors">
                                    📅 Reverse Schedule
                                </button>
                            </div>
                        </div>
                        <textarea name="description" id="project_description"><?php echo htmlspecialchars($assignment_to_edit['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="text-right">
                         <a href="assignments.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300">Cancel</a>
                         <button type="submit" name="<?php echo $action === 'create' ? 'add_assignment' : 'update_assignment'; ?>" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">
                            Save
                        </button>
                    </div>
                </div>
            </form>
        </div>

    <?php else: ?>
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold">Assignments</h2>
            </div>
            <div class="flex space-x-2">
                <a href="assignments.php?action=create" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">Create Assignment</a>
                <a href="assignments.php" class="bg-gray-800 text-white px-5 py-2 rounded-lg font-semibold hover:bg-gray-900">Assignments List</a>
            </div>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($assignments)): ?>
                <p class="col-span-full text-gray-500">No assignments found. Click "Create Assignment" to get started.</p>
            <?php else: ?>
                <?php 
                    $colors = ['bg-green-500', 'bg-blue-500', 'bg-red-500', 'bg-yellow-500', 'bg-indigo-500'];
                    $color_index = 0;
                ?>
                <?php foreach ($assignments as $assignment): ?>
                <?php
                    $color_class = $colors[$color_index % count($colors)];
                    $color_index++;
                    $initial = strtoupper(substr($assignment['course'], 0, 1));
                ?>
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 <?php echo $color_class; ?> rounded-lg flex items-center justify-center text-white font-bold text-xl mr-4">
                                    <?php echo $initial; ?>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($assignment['course']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($assignment['title']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="assignments.php?action=edit&id=<?php echo $assignment['id']; ?>" class="text-gray-400 hover:text-blue-500">
                                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="assignments.php" onsubmit="return confirm('Are you sure you want to delete this assignment?');" class="inline">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                    <button type="submit" name="delete_assignment" class="text-gray-400 hover:text-red-500 ml-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">
                            <?php echo htmlspecialchars(strip_tags($assignment['description'])); ?>
                        </p>
                    </div>
                    <div class="flex items-center justify-between mt-6">
                        <div class="flex -space-x-2">
                             <?php if (!empty($assignment['team_members'])): ?>
                                <?php $members = explode(', ', $assignment['team_members']); ?>
                                <?php foreach(array_slice($members, 0, 3) as $member): ?>
                                    <div class="w-7 h-7 bg-gray-200 rounded-full border-2 border-white flex items-center justify-center text-gray-600 font-semibold text-xs" title="<?php echo htmlspecialchars($member); ?>">
                                        <?php echo strtoupper(substr($member, 0, 2)); ?>
                                    </div>
                                <?php endforeach; ?>
                             <?php endif; ?>
                        </div>
                        <div class="flex items-center space-x-2 text-xs font-semibold">
                            <?php
                                $status_class = '';
                                switch (strtoupper($assignment['status'])) {
                                    case 'PENDING': $status_class = 'bg-yellow-100 text-yellow-800'; break;
                                    case 'STARTED': $status_class = 'bg-blue-100 text-blue-800'; break;
                                    case 'FINISHED': $status_class = 'bg-green-100 text-green-800'; break;
                                }
                            ?>
                            <span class="px-2 py-1 rounded-md <?php echo $status_class; ?>"><?php echo htmlspecialchars($assignment['status']); ?></span>
                            <?php if ($assignment['end_date']): ?>
                            <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-600"><?php echo date("d M Y", strtotime($assignment['end_date'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    // --- AI AUTO-OUTLINING ---
    async function generateAIOutline() {
        const editor = tinymce.get('project_description');
        const promptText = editor.getContent({format: 'text'}).trim();
        const title = document.querySelector('input[name="title"]').value.trim();

        if (!promptText && !title) {
            alert('Please enter an assignment title or paste a prompt into the description first.');
            return;
        }

        const btn = document.querySelector('button[onclick="generateAIOutline()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = "⏳ Generating...";
        btn.disabled = true;

        try {
            const prompt = `Act as an academic tutor. Create a detailed starter outline for the following assignment. 
            Assignment Title: "${title}"
            Assignment Prompt: "${promptText}"
            
            Please return the outline in basic HTML format (using <h3>, <ul>, <li>, <strong>). Do not wrap the response in markdown backticks.`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            let reply = data.reply.replace(/```html/g, '').replace(/```/g, '').trim();
            editor.insertContent('<br><br><h3>--- AI Generated Outline ---</h3><br>' + reply);

        } catch (error) {
            console.error('AI Outline Error:', error);
            alert('Failed to generate outline: ' + error.message);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // --- AI REVERSE SCHEDULING ---
    async function generateReverseSchedule() {
        const title = document.querySelector('input[name="title"]').value.trim();
        const endDate = document.querySelector('input[name="end_date"]').value;
        const editor = tinymce.get('project_description');
        const promptText = editor.getContent({format: 'text'}).trim();

        if (!title || !endDate) {
            alert('Please enter a Title and an End Date to generate a reverse schedule.');
            return;
        }

        const btn = document.querySelector('button[onclick="generateReverseSchedule()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = "⏳ Scheduling...";
        btn.disabled = true;

        try {
            const today = new Date().toISOString().split('T')[0];
            const prompt = `Act as a productivity assistant. I have an assignment called "${title}" due on "${endDate}". Today is "${today}". The description is: "${promptText}".
            
            Please generate a reverse schedule with 3-5 milestones to help me finish on time.
            
            CRITICAL: You MUST respond ONLY with a valid JSON array of objects. Do not wrap it in markdown blockquotes like \`\`\`json. Return raw JSON.
            Each object must have exactly two keys: "title" (string) and "finish_by" (YYYY-MM-DD string). Example: [{"title": "Research", "finish_by": "2023-10-15"}]`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            // Parse the JSON
            let jsonStr = data.reply.replace(/```json/g, '').replace(/```/g, '').trim();
            let tasks;
            try {
                tasks = JSON.parse(jsonStr);
            } catch(e) {
                throw new Error("AI did not return a valid task array. Raw response: " + jsonStr);
            }

            if (!Array.isArray(tasks)) throw new Error("AI did not return an array.");

            // Send tasks to backend to add to DB
            const formData = new FormData();
            formData.append('ajax_add_tasks', '1');
            formData.append('tasks', JSON.stringify(tasks));
            formData.append('assignment_title', title);

            const saveResponse = await fetch('assignments.php', {
                method: 'POST',
                body: formData
            });

            const saveData = await saveResponse.json();
            if (saveData.success) {
                alert(`Successfully created ${tasks.length} sub-tasks and added them to your To-do list!`);
                
                // Append to description so user can see it in the assignment view
                let scheduleHtml = '<br><br><h3>--- AI Reverse Schedule (Added to To-Dos) ---</h3><ul>';
                tasks.forEach(t => {
                    scheduleHtml += `<li><strong>${t.finish_by}:</strong> ${t.title}</li>`;
                });
                scheduleHtml += '</ul><br>';
                editor.insertContent(scheduleHtml);
            } else {
                throw new Error(saveData.error || "Failed to save tasks to database.");
            }

        } catch (error) {
            console.error('Reverse Schedule Error:', error);
            alert('Failed to generate schedule: ' + error.message);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>