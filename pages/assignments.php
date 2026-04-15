<?php
// /pages/assignments.php - Updated with full-page form

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
    // --- CRUD LOGIC ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!csrf_verify()) { $db_error = "Security token mismatch. Please try again."; goto render; }
        // CREATE
        if (isset($_POST['add_assignment'])) {
            // New fields from form
            $title = trim($_POST['title']);
            $related_goal_id = !empty($_POST['related_goal_id']) ? intval($_POST['related_goal_id']) : null;
            $status = trim($_POST['status']);
            $summary = trim($_POST['summary']);
            $team_members = isset($_POST['team_members']) ? implode(', ', $_POST['team_members']) : '';
            $start_date = !empty($_POST['start_date']) ? trim($_POST['start_date']) : null;
            $end_date = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
            $description = trim($_POST['description']);
            
            // Note: The 'course' field is no longer in the new form, setting it based on title for now.
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

            $sql = "UPDATE assignments SET title = ?, course = ?, description = ?, start_date = ?, end_date = ?, related_goal_id = ?, team_members = ?, status = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssissiii", $title, $course, $description, $start_date, $end_date, $related_goal_id, $team_members, $status, $assignment_id, $user_id, $tenant_id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: assignments.php?success=2");
                    exit();
                } else { $db_error = "Error updating assignment: " . mysqli_stmt_error($stmt); }
            }
        }
        // DELETE
        if (isset($_POST['delete_assignment'])) {
            // ... (delete logic can be added here if needed on the edit page)
        }
    }

    // --- READ DATA ---
    if ($action == 'list') {
        $sql = "SELECT * FROM assignments WHERE user_id = ? AND tenant_id = ? ORDER BY end_date ASC";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $assignments[] = $row; }
        }
    }
    
    // If showing form, fetch data for dropdowns
    if ($action == 'create' || $action == 'edit') {
        // Fetch study goals for 'Related Goal' dropdown
        $sql_goals = "SELECT id, title FROM study_goals WHERE user_id = ? AND tenant_id = ? AND is_completed = 0 ORDER BY title ASC";
        if ($stmt = mysqli_prepare($link, $sql_goals)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $study_goals[] = $row; }
        }

        // Fetch contacts for 'Team Members' list
        $sql_contacts = "SELECT id, name FROM contacts WHERE user_id = ? AND tenant_id = ? ORDER BY name ASC";
        if ($stmt = mysqli_prepare($link, $sql_contacts)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $contacts[] = $row; }
        }
        
        // If editing, fetch the specific assignment's details
        if ($action == 'edit' && $edit_id) {
            $sql = "SELECT * FROM assignments WHERE id = ? AND user_id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $edit_id, $user_id, $tenant_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $assignment_to_edit = mysqli_fetch_assoc($result);
            }
        }
    }
}

render:
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
            <form method="POST"><?php echo csrf_field(); ?>
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="assignment_id" value="<?php echo $assignment_to_edit['id']; ?>">
                <?php endif; ?>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">TITLE*</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($assignment_to_edit['title'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RELATED GOAL</label>
                            <select name="related_goal_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
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
                            <select name="status" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="PENDING" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['status'] == 'PENDING') ? 'selected' : ''; ?>>Pending</option>
                                <option value="STARTED" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['status'] == 'STARTED') ? 'selected' : ''; ?>>Started</option>
                                <option value="FINISHED" <?php echo (isset($assignment_to_edit) && $assignment_to_edit['status'] == 'FINISHED') ? 'selected' : ''; ?>>Finished</option>
                            </select>
                        </div>
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-gray-700">ASSIGNMENT SUMMARY</label>
                         <textarea name="summary" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SELECT TEAM / GROUP MEMBERS</label>
                        <select name="team_members[]" multiple class="mt-1 block w-full border border-gray-300 rounded-md p-2 h-32">
                            <?php 
                                $selected_members = isset($assignment_to_edit) ? explode(', ', $assignment_to_edit['team_members']) : [];
                                foreach ($contacts as $contact): 
                            ?>
                                <option value="<?php echo htmlspecialchars($contact['name']); ?>" <?php echo in_array($contact['name'], $selected_members) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">START DATE</label>
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($assignment_to_edit['start_date'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">END DATE</label>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($assignment_to_edit['end_date'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-gray-700">PROJECT DESCRIPTION</label>
                            <button type="button" onclick="openAIDescModal()" class="bg-green-100 text-green-800 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-green-200">
                                <i data-lucide="sparkles" class="w-3 h-3 inline-block mr-1"></i>AI Generate
                            </button>
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
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            <?php echo htmlspecialchars($assignment['description']); ?>
                        </p>
                    </div>
                    <div class="flex items-center justify-between mt-6">
                        <div class="flex -space-x-2">
                             <?php if (!empty($assignment['team_members'])): ?>
                                <?php $members = explode(', ', $assignment['team_members']); ?>
                                <?php foreach(array_slice($members, 0, 3) as $member): ?>
                                    <div class="w-7 h-7 bg-gray-200 rounded-full border-2 border-white flex items-center justify-center text-gray-600 font-semibold text-xs">
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
                            <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-600"><?php echo date("d M Y", strtotime($assignment['end_date'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<!-- AI Description Modal -->
<div id="aiDescModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-green-600"></i> AI Generate Description</h2>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Briefly describe your assignment</label>
            <textarea id="ai-desc-context" rows="3" class="w-full border rounded-md p-2" placeholder="e.g. Research paper on climate change for Environmental Science, due in 2 weeks..."></textarea>
        </div>
        <div id="ai-desc-error" class="hidden bg-red-100 text-red-700 px-3 py-2 rounded mb-4 text-sm"></div>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('aiDescModal').classList.add('hidden')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            <button type="button" id="ai-desc-btn" onclick="generateAssignmentDesc()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Generate</button>
        </div>
    </div>
</div>

<script>
function openAIDescModal() {
    document.getElementById('ai-desc-context').value = '';
    document.getElementById('ai-desc-error').classList.add('hidden');
    document.getElementById('aiDescModal').classList.remove('hidden');
}

async function generateAssignmentDesc() {
    const context = document.getElementById('ai-desc-context').value.trim();
    const btn     = document.getElementById('ai-desc-btn');
    const errDiv  = document.getElementById('ai-desc-error');
    if (!context) return;
    errDiv.classList.add('hidden');
    btn.disabled = true;
    btn.textContent = 'Generating...';

    try {
        const res = await fetch('<?php echo BASE_URL; ?>/api/ai-assist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
            body: JSON.stringify({ feature: 'assignment_describe', context })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        if (typeof tinymce !== 'undefined' && tinymce.get('project_description')) {
            tinymce.get('project_description').setContent(data.result.replace(/\n/g, '<br>'));
        }
        document.getElementById('aiDescModal').classList.add('hidden');
    } catch (err) {
        errDiv.textContent = 'Error: ' + err.message;
        errDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Generate';
    }
}
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>