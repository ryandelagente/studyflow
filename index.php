<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect them to the login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// /index.php (Updated with Authentication and SAAS Fallbacks)

// --- SETUP ---
require_once(__DIR__ . '/config.php');

// --- INITIALIZATION ---
$db_error = null;
$user_id = $_SESSION["id"]; 
$tenant_id = $_SESSION["tenant_id"] ?? 0; // Fallback to 0 if undefined to prevent warnings

// --- DATA FOR WIDGETS ---
$task_stats = ['total' => 0, 'completed' => 0, 'percentage' => 0];
$goal_stats = ['total' => 0, 'completed' => 0, 'percentage' => 0];
$assignment_stats = ['total' => 0, 'completed' => 0, 'percentage' => 0];
$recent_tasks = [];
$recent_assignments = [];
$studied_today_seconds = 0;


// Check for DB connection
if (!isset($link) || $link === false) {
    $db_error = "ERROR: Could not connect to the database. " . mysqli_connect_error();
} else {
    
    // 1. Fetch Task Statistics for the current user and tenant
    $sql = "SELECT COUNT(id) AS total, SUM(IF(is_completed = 1, 1, 0)) AS completed FROM tasks WHERE user_id = ? AND tenant_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        if ($data) {
            $task_stats['total'] = $data['total'] ?? 0;
            $task_stats['completed'] = $data['completed'] ?? 0;
            if ($task_stats['total'] > 0) {
                $task_stats['percentage'] = round(($task_stats['completed'] / $task_stats['total']) * 100);
            }
        }
        mysqli_stmt_close($stmt);
    }

    // 2. Fetch Study Goal Statistics
    $sql = "SELECT COUNT(id) AS total, SUM(IF(is_completed = 1, 1, 0)) AS completed FROM study_goals WHERE user_id = ? AND tenant_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        if ($data) {
            $goal_stats['total'] = $data['total'] ?? 0;
            $goal_stats['completed'] = $data['completed'] ?? 0;
            if ($goal_stats['total'] > 0) {
                $goal_stats['percentage'] = round(($goal_stats['completed'] / $goal_stats['total']) * 100);
            }
        }
        mysqli_stmt_close($stmt);
    }

    // 3. Fetch Assignment Statistics
    $sql = "SELECT COUNT(id) AS total, SUM(IF(status = 'FINISHED', 1, 0)) AS completed FROM assignments WHERE user_id = ? AND tenant_id = ?";
     if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        if ($data) {
            $assignment_stats['total'] = $data['total'] ?? 0;
            $assignment_stats['completed'] = $data['completed'] ?? 0;
            if ($assignment_stats['total'] > 0) {
                $assignment_stats['percentage'] = round(($assignment_stats['completed'] / $assignment_stats['total']) * 100);
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    // 4. Fetch Recent Incomplete Tasks
    $sql = "SELECT title, priority FROM tasks WHERE user_id = ? AND tenant_id = ? AND is_completed = 0 ORDER BY created_at DESC LIMIT 4";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) { $recent_tasks[] = $row; }
        mysqli_stmt_close($stmt);
    }
    
    // 5. Fetch Today's Total Study Time
    $sql = "SELECT COALESCE(SUM(duration_seconds), 0) AS total FROM study_sessions WHERE user_id = ? AND tenant_id = ? AND DATE(created_at) = CURDATE()";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        $studied_today_seconds = $data['total'] ?? 0;
        mysqli_stmt_close($stmt);
    }

    // 6. Fetch Recent Pending Assignments
    $sql = "SELECT title, course, status FROM assignments WHERE user_id = ? AND tenant_id = ? AND status != 'FINISHED' ORDER BY end_date ASC LIMIT 3";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) { $recent_assignments[] = $row; }
        mysqli_stmt_close($stmt);
    }

    // Close the connection
    mysqli_close($link);
}

// --- RENDER PAGE ---
require_once(BASE_PATH . '/partials/header.php');
?>
            
<main class="flex-1 p-8 overflow-y-auto">
    <?php if ($db_error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Database Error!</strong>
            <span class="block sm:inline"><?php echo $db_error; ?></span>
        </div>
    <?php else: ?>
        <div class="mb-8">
            <h2 class="text-3xl font-bold">Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <p class="text-gray-500">“An investment in knowledge pays the best interest.” - Benjamin Franklin</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold text-lg">Assignments</h3>
                <div class="flex items-center justify-between mt-4">
                    <div>
                        <p class="text-3xl font-bold"><?php echo $assignment_stats['total']; ?></p>
                        <p class="text-gray-500 text-sm">Total</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold"><?php echo $assignment_stats['completed']; ?></p>
                        <p class="text-gray-500 text-sm">Completed</p>
                    </div>
                </div>
                 <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $assignment_stats['percentage']; ?>%"></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow col-span-1 md:col-span-2">
                <h3 class="font-semibold text-lg">Studied Today</h3>
                <?php
                    $h = floor($studied_today_seconds / 3600);
                    $m = floor(($studied_today_seconds % 3600) / 60);
                    if ($h > 0) {
                        echo '<p class="text-3xl font-bold">' . $h . '<span class="text-lg text-gray-500">h </span>' . $m . '<span class="text-lg text-gray-500">m</span></p>';
                    } else {
                        echo '<p class="text-3xl font-bold">' . $m . '<span class="text-lg text-gray-500">m</span></p>';
                    }
                ?>
                <div class="h-24 mt-4 flex items-end space-x-2">
                    <div class="w-full bg-purple-200 h-1/4 rounded-t-md"></div> <div class="w-full bg-purple-200 h-1/2 rounded-t-md"></div>
                    <div class="w-full bg-purple-200 h-3/4 rounded-t-md"></div> <div class="w-full bg-purple-600 h-full rounded-t-md"></div>
                    <div class="w-full bg-purple-200 h-2/3 rounded-t-md"></div> <div class="w-full bg-purple-200 h-1/2 rounded-t-md"></div>
                    <div class="w-full bg-purple-200 h-1/3 rounded-t-md"></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow row-span-2">
                <h3 class="font-semibold text-lg">To-do List</h3>
                <p class="text-gray-500 text-sm">Your most recent incomplete tasks</p>
                <ul class="mt-4 space-y-4">
                    <?php if (empty($recent_tasks)): ?>
                        <li class="text-center text-gray-500 py-4">No pending tasks. Great job!</li>
                    <?php else: ?>
                        <?php foreach ($recent_tasks as $task): ?>
                        <li class="flex items-start">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-md mt-1"></div>
                            <div class="ml-3">
                                <p class="font-medium text-sm"><?php echo htmlspecialchars($task['title']); ?></p>
                                <?php
                                    $p_class = 'bg-red-100 text-red-800';
                                    if ($task['priority'] == 'LOW') { $p_class = 'bg-green-100 text-green-800'; }
                                    elseif ($task['priority'] == 'MEDIUM') { $p_class = 'bg-yellow-100 text-yellow-800'; }
                                ?>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?php echo $p_class; ?>"><?php echo htmlspecialchars($task['priority']); ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-semibold text-lg">Study Goals</h3>
                <div class="flex items-center justify-between mt-4">
                     <div>
                        <p class="text-3xl font-bold"><?php echo $goal_stats['total']; ?></p>
                        <p class="text-gray-500 text-sm">Total Goals</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold"><?php echo $goal_stats['completed']; ?></p>
                        <p class="text-gray-500 text-sm">Completed</p>
                    </div>
                </div>
                 <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $goal_stats['percentage']; ?>%"></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow col-span-1 md:col-span-2">
                <h3 class="font-semibold text-lg">Total Tasks Completion</h3>
                <div class="flex items-center justify-between mt-4">
                     <div>
                        <p class="text-3xl font-bold"><?php echo $task_stats['total']; ?></p>
                        <p class="text-gray-500 text-sm">Total Tasks</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold"><?php echo $task_stats['completed']; ?></p>
                        <p class="text-gray-500 text-sm">Completed</p>
                    </div>
                </div>
                 <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $task_stats['percentage']; ?>%"></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow col-span-1 md:col-span-2 lg:col-span-4">
                <h3 class="font-semibold text-lg">Upcoming Assignments</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                   <?php if (empty($recent_assignments)): ?>
                        <p class="text-gray-500 col-span-full">No upcoming assignments. You're all caught up!</p>
                   <?php else: ?>
                        <?php foreach($recent_assignments as $assignment): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                           <p class="font-bold truncate"><?php echo htmlspecialchars($assignment['title']); ?></p>
                           <p class="text-sm text-gray-600 mt-2">Course: <?php echo htmlspecialchars($assignment['course']); ?></p>
                           <div class="flex items-center justify-between mt-4 text-xs">
                                <?php
                                    $s_class = 'bg-yellow-100 text-yellow-800';
                                    if ($assignment['status'] == 'STARTED') { $s_class = 'bg-blue-100 text-blue-800'; }
                                ?>
                               <span class="px-2 py-1 <?php echo $s_class; ?> rounded-full font-semibold"><?php echo htmlspecialchars($assignment['status']); ?></span>
                           </div>
                       </div>
                       <?php endforeach; ?>
                   <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
// Include the footer
require_once(BASE_PATH . '/partials/footer.php');
?>