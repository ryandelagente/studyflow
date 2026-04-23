<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// /pages/calendar.php

// Use an absolute path for reliable file inclusion
require_once(__DIR__ . '/../config.php');

// --- SAAS FIX: Use tenant_id and user_id from session ---
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"];

// Initialize variables
$db_error = null;
$success_message = null;
$events = [];

// --- AJAX HANDLER FOR AI AUTO-SCHEDULING ---
if (isset($_POST['ajax_add_events'])) {
    header('Content-Type: application/json');
    $events_json = json_decode($_POST['events'], true);
    
    if (is_array($events_json)) {
        $added = 0;
        $sql = "INSERT INTO events (user_id, tenant_id, title, description, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            foreach ($events_json as $e) {
                $t = $e['title'] ?? 'AI Study Session';
                $d = $e['description'] ?? 'Automatically scheduled study block.';
                $st = $e['start_time'] ?? null;
                $et = $e['end_time'] ?? null;
                
                if ($st && $et) {
                    mysqli_stmt_bind_param($stmt, "iissss", $user_id, $tenant_id, $t, $d, $st, $et);
                    if (mysqli_stmt_execute($stmt)) {
                        $added++;
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }
        echo json_encode(['success' => true, 'added' => $added]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON returned by AI']);
    }
    exit;
}


// --- Date & Time Setup for the Calendar Grid ---
date_default_timezone_set('UTC'); // Set a default timezone

// Determine the target date for the calendar view (from URL or today)
if (isset($_GET['week'])) {
    try {
        $target_date = new DateTime($_GET['week']);
    } catch (Exception $e) {
        $target_date = new DateTime(); // Default to today on invalid date
    }
} else {
    $target_date = new DateTime(); // Default to today
}
$today = new DateTime(); // Keep today's date for highlighting

$day_of_week = $target_date->format('w'); // 0 (for Sunday) through 6 (for Saturday)

// Find the start of the week (Sunday)
$start_of_week = clone $target_date;
$start_of_week->modify('-' . $day_of_week . ' days');

// Create an array of dates for the current week
$week_dates = [];
for ($i = 0; $i < 7; $i++) {
    $date = clone $start_of_week;
    $date->modify('+' . $i . ' days');
    $week_dates[] = $date;
}
$week_start_str = $week_dates[0]->format('Y-m-d 00:00:00');
$week_end_str = $week_dates[6]->format('Y-m-d 23:59:59');

// --- Navigation Links ---
$prev_week = clone $target_date;
$prev_week->modify('-7 days');
$prev_week_link = 'calendar.php?week=' . $prev_week->format('Y-m-d');

$next_week = clone $target_date;
$next_week->modify('+7 days');
$next_week_link = 'calendar.php?week=' . $next_week->format('Y-m-d');

$today_link = 'calendar.php';


if (!isset($link) || !$link) {
    $db_error = "Database connection failed.";
} else {
    // --- CRUD LOGIC ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax_add_events'])) {
        // CREATE
        if (isset($_POST['add_event'])) {
            $title = trim($_POST['title']); $description = trim($_POST['description']); $start_time = trim($_POST['start_time']); $end_time = trim($_POST['end_time']);
            $sql = "INSERT INTO events (user_id, tenant_id, title, description, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iissss", $user_id, $tenant_id, $title, $description, $start_time, $end_time);
                if (mysqli_stmt_execute($stmt)) { header("Location: calendar.php?success=1&week=".$start_time); exit(); } 
                else { $db_error = "Error creating event: " . mysqli_stmt_error($stmt); }
                mysqli_stmt_close($stmt);
            }
        }
        // UPDATE
        if (isset($_POST['edit_event'])) {
            $event_id = $_POST['event_id']; $title = trim($_POST['title']); $description = trim($_POST['description']); $start_time = trim($_POST['start_time']); $end_time = trim($_POST['end_time']);
            $sql = "UPDATE events SET title = ?, description = ?, start_time = ?, end_time = ? WHERE id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssssii", $title, $description, $start_time, $end_time, $event_id, $tenant_id);
                if (mysqli_stmt_execute($stmt)) { header("Location: calendar.php?success=2&week=".$start_time); exit(); } 
                else { $db_error = "Error updating event: " . mysqli_stmt_error($stmt); }
                mysqli_stmt_close($stmt);
            }
        }
        // DELETE
        if (isset($_POST['delete_event'])) {
            $event_id = $_POST['event_id'];
            $sql = "DELETE FROM events WHERE id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $event_id, $tenant_id);
                if (mysqli_stmt_execute($stmt)) { header("Location: calendar.php?success=3&week=".$_GET['week']); exit(); } 
                else { $db_error = "Error deleting event: " . mysqli_stmt_error($stmt); }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 1: $success_message = "Event added successfully."; break;
            case 2: $success_message = "Event updated successfully."; break;
            case 3: $success_message = "Event deleted successfully."; break;
        }
    }

    // --- READ (Fetch events for the current tenant's week) ---
    $sql = "SELECT id, title, description, start_time, end_time FROM events WHERE tenant_id = ? AND start_time <= ? AND end_time >= ? ORDER BY start_time ASC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iss", $tenant_id, $week_end_str, $week_start_str);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $events[] = $row; }
            mysqli_free_result($result);
        } else { $db_error = "Error fetching events: " . mysqli_error($link); }
        mysqli_stmt_close($stmt);
    }

    // --- FETCH CONTEXT FOR AI ---
    $ai_tasks = [];
    $ai_assignments = [];
    if ($link) {
        $res = mysqli_query($link, "SELECT title, priority, finish_by FROM tasks WHERE tenant_id = $tenant_id AND is_completed = 0 AND finish_by >= CURDATE() ORDER BY finish_by ASC LIMIT 10");
        if($res) while($r = mysqli_fetch_assoc($res)) $ai_tasks[] = $r;

        $res2 = mysqli_query($link, "SELECT title, course, end_date FROM assignments WHERE tenant_id = $tenant_id AND status != 'FINISHED' AND end_date >= CURDATE() ORDER BY end_date ASC LIMIT 5");
        if($res2) while($r = mysqli_fetch_assoc($res2)) $ai_assignments[] = $r;
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    
    <div id="ai-briefing-banner" class="mb-6 bg-gradient-to-r from-purple-100 to-indigo-50 border border-purple-200 rounded-xl p-5 shadow-sm hidden">
        <div class="flex items-start space-x-4">
            <div class="bg-white p-2 rounded-full shadow-sm">
                <span class="text-2xl">✨</span>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-800 mb-1">Daily AI Briefing</h3>
                <p id="ai-briefing-text" class="text-gray-600 text-sm leading-relaxed">Generating your morning briefing...</p>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center mb-8">
         <div>
            <h2 class="text-3xl font-bold">Calendar</h2>
        </div>
        <div class="flex space-x-2">
            <button onclick="autoScheduleStudyBlocks()" id="auto-schedule-btn" class="bg-indigo-100 text-indigo-700 px-5 py-2 rounded-lg font-semibold hover:bg-indigo-200 transition-colors">
                🪄 Auto-Schedule Study Blocks
            </button>
            <button onclick="openModal('add-event-modal')" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                Add Event
            </button>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-800">
            <?php echo $week_dates[0]->format('M j'); ?> &ndash; <?php echo $week_dates[6]->format('j, Y'); ?>
        </h3>
        <div class="flex items-center space-x-4">
            <div class="flex rounded-lg border bg-white p-1 space-x-1">
                <a href="#" class="px-3 py-1 text-sm rounded-md hover:bg-gray-100">month</a>
                <a href="#" class="px-3 py-1 text-sm rounded-md bg-gray-200 font-semibold">week</a>
                <a href="#" class="px-3 py-1 text-sm rounded-md hover:bg-gray-100">day</a>
                <a href="#" class="px-3 py-1 text-sm rounded-md hover:bg-gray-100">list</a>
            </div>
             <div class="flex rounded-md border bg-white">
                <a href="<?php echo $prev_week_link; ?>" class="px-3 py-1.5 text-sm hover:bg-gray-100 rounded-l-md">&lt;</a>
                <a href="<?php echo $today_link; ?>" class="px-3 py-1.5 text-sm border-l border-r hover:bg-gray-100">today</a>
                <a href="<?php echo $next_week_link; ?>" class="px-3 py-1.5 text-sm hover:bg-gray-100 rounded-r-md">&gt;</a>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($db_error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><strong>Error:</strong> <?php echo $db_error; ?></div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-[auto,1fr,1fr,1fr,1fr,1fr,1fr,1fr]">
            <div class="border-r border-b"></div> <?php foreach ($week_dates as $date): ?>
                <div class="text-center p-2 border-b <?php echo $date->format('Y-m-d') == $today->format('Y-m-d') ? 'bg-yellow-50' : ''; ?>">
                    <p class="font-semibold text-sm text-gray-600"><?php echo $date->format('D n/j'); ?></p>
                </div>
            <?php endforeach; ?>
            
            <div class="row-start-2 text-xs text-center text-gray-500 border-r border-b p-2">all-day</div>
            <?php for ($i = 0; $i < 7; $i++): ?>
                <div class="row-start-2 border-b border-r h-8 <?php echo $week_dates[$i]->format('Y-m-d') == $today->format('Y-m-d') ? 'bg-yellow-50' : ''; ?>"></div>
            <?php endfor; ?>


            <div class="col-start-1 col-end-9 row-start-3 grid grid-cols-[auto,1fr,1fr,1fr,1fr,1fr,1fr,1fr] relative">
                <div class="row-start-1" style="grid-row: 1 / span 24;">
                    <?php for ($hour = 0; $hour < 24; $hour++): ?>
                        <div class="h-16 text-right pr-2 text-xs text-gray-500 border-r -mt-2.5">
                            <?php if ($hour > 0): echo date("g A", strtotime("$hour:00")); endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <div class="col-start-<?php echo $i + 2; ?> row-start-1 border-r <?php echo $week_dates[$i]->format('Y-m-d') == $today->format('Y-m-d') ? 'bg-yellow-50' : ''; ?>" style="grid-row: 1 / span 24;">
                         <?php for ($hour = 1; $hour < 24; $hour++): ?>
                             <div class="h-16 border-t"></div>
                         <?php endfor; ?>
                    </div>
                <?php endfor; ?>

                <?php foreach($events as $event): ?>
                    <?php
                        $start = new DateTime($event['start_time']); $end = new DateTime($event['end_time']);
                        $day_index = (int)$start->format('w'); $col = $day_index + 2;
                        $start_minute = $start->format('H') * 60 + $start->format('i');
                        $end_minute = $end->format('H') * 60 + $end->format('i');
                        $duration = max(30, $end_minute - $start_minute); // Min duration of 30 mins
                        
                        $top_offset = ($start_minute / 60) * 64; // 64px (h-16) per hour
                        $height = ($duration / 60) * 64;
                        
                        // Slightly different color for AI generated study sessions
                        $bg_color = strpos(strtolower($event['title']), 'study') !== false ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-purple-500 hover:bg-purple-600';
                    ?>
                    <div class="absolute w-full px-1" style="grid-column: <?php echo $col; ?>; top: <?php echo $top_offset; ?>px;">
                         <div class="<?php echo $bg_color; ?> text-white rounded p-1 text-xs overflow-hidden cursor-pointer edit-event-btn transition-colors shadow-sm"
                              style="height: <?php echo $height; ?>px;"
                              data-id="<?php echo $event['id']; ?>"
                              data-title="<?php echo htmlspecialchars($event['title']); ?>"
                              data-description="<?php echo htmlspecialchars($event['description']); ?>"
                              data-start-time="<?php echo date('Y-m-d\TH:i', strtotime($event['start_time'])); ?>"
                              data-end-time="<?php echo date('Y-m-d\TH:i', strtotime($event['end_time'])); ?>">
                            <p class="font-bold truncate"><?php echo htmlspecialchars($event['title']); ?></p>
                            <p><?php echo date("g:i a", strtotime($event['start_time'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<div id="add-event-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Add New Event</h3>
        <form action="calendar.php?week=<?php echo $target_date->format('Y-m-d'); ?>" method="post">
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium">Start Time</label>
                    <input type="datetime-local" name="start_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">End Time</label>
                    <input type="datetime-local" name="end_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('add-event-modal')" class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" name="add_event" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Add Event</button>
            </div>
        </form>
    </div>
</div>
<div id="edit-event-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Edit Event</h3>
        <form action="calendar.php?week=<?php echo $target_date->format('Y-m-d'); ?>" method="post">
            <input type="hidden" name="event_id" id="edit_event_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" id="edit_title" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" id="edit_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium">Start Time</label>
                    <input type="datetime-local" name="start_time" id="edit_start_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">End Time</label>
                    <input type="datetime-local" name="end_time" id="edit_end_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
                </div>
            </div>
            <div class="flex justify-between items-center">
                 <button type="submit" name="delete_event" class="text-red-600 hover:underline font-medium" onclick="return confirm('Are you sure you want to delete this event?');">Delete Event</button>
                <div class="flex space-x-4">
                    <button type="button" onclick="closeModal('edit-event-modal')" class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" name="edit_event" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Context Data for AI ---
    const userTasks = <?php echo json_encode($ai_tasks); ?>;
    const userAssignments = <?php echo json_encode($ai_assignments); ?>;
    const weekEvents = <?php echo json_encode($events); ?>;
    const currentWeekStart = "<?php echo $week_start_str; ?>";
    const todayStr = "<?php echo date('Y-m-d'); ?>";

    // --- Modal Handling ---
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }
    
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-event-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('edit_event_id').value = this.dataset.id;
                document.getElementById('edit_title').value = this.dataset.title;
                document.getElementById('edit_description').value = this.dataset.description;
                document.getElementById('edit_start_time').value = this.dataset.startTime;
                document.getElementById('edit_end_time').value = this.dataset.endTime;
                openModal('edit-event-modal');
            });
        });

        // Trigger Daily Briefing
        generateDailyBriefing();
    });

    // --- AI DAILY BRIEFING ---
    async function generateDailyBriefing() {
        const banner = document.getElementById('ai-briefing-banner');
        const textEl = document.getElementById('ai-briefing-text');
        
        // Only show if there are actual tasks or assignments to talk about
        if (userTasks.length === 0 && userAssignments.length === 0) {
            return; 
        }
        
        banner.classList.remove('hidden');

        try {
            const prompt = `You are a helpful study assistant. Today is ${todayStr}. 
            The student has these pending tasks: ${JSON.stringify(userTasks)}
            And these upcoming assignments: ${JSON.stringify(userAssignments)}
            
            Write a very short, motivating 2-3 sentence morning briefing suggesting what they should focus on today based on priority and deadlines. Do not use formatting like bolding or bullet points. Keep it conversational.`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt, chat_id: null }) // We don't need to save this to chat history necessarily, but the API will just create a new session if null.
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            textEl.textContent = data.reply;
        } catch (error) {
            console.error('AI Briefing Error:', error);
            textEl.textContent = "Welcome back! Keep up the great work studying today.";
        }
    }

    // --- AI SMART STUDY SCHEDULING ---
    async function autoScheduleStudyBlocks() {
        const btn = document.getElementById('auto-schedule-btn');
        if (userTasks.length === 0 && userAssignments.length === 0) {
            alert('You have no pending tasks or assignments to schedule study blocks for!');
            return;
        }

        const originalText = btn.innerHTML;
        btn.innerHTML = "⏳ Scheduling...";
        btn.disabled = true;

        try {
            const prompt = `You are a smart scheduling assistant. The student needs to study for their upcoming assignments and tasks. 
            Today is ${todayStr}. The current week starts on ${currentWeekStart}.
            
            Here is their current schedule (busy times) for this week: ${JSON.stringify(weekEvents)}.
            Here are their pending tasks: ${JSON.stringify(userTasks)}.
            Here are their assignments: ${JSON.stringify(userAssignments)}.
            
            Find 2 to 3 empty gaps (1-2 hours each) in the current schedule during typical waking hours (8am - 8pm) and suggest specific study blocks for their specific tasks/assignments.
            
            CRITICAL: Return ONLY a valid JSON array of objects. Do not wrap it in markdown blockquotes like \`\`\`json. Return raw JSON.
            Each object must have exactly four keys: 
            "title" (string, e.g., "Study: Math Assignment"),
            "description" (string, e.g., "Focus on chapter 4"),
            "start_time" (string, exact format "YYYY-MM-DD HH:MM:00"),
            "end_time" (string, exact format "YYYY-MM-DD HH:MM:00")`;

            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            let jsonStr = data.reply.replace(/```json/g, '').replace(/```/g, '').trim();
            let newEvents;
            try {
                newEvents = JSON.parse(jsonStr);
            } catch(e) {
                throw new Error("AI did not return a valid JSON array. Raw response: " + jsonStr);
            }

            if (!Array.isArray(newEvents)) throw new Error("AI did not return an array.");

            // Send new events to backend to add to DB
            const formData = new FormData();
            formData.append('ajax_add_events', '1');
            formData.append('events', JSON.stringify(newEvents));

            const saveResponse = await fetch('calendar.php', {
                method: 'POST',
                body: formData
            });

            const saveData = await saveResponse.json();
            if (saveData.success) {
                alert(`Successfully found gaps and scheduled ${saveData.added} study blocks!`);
                window.location.reload(); // Reload to show new events on the calendar
            } else {
                throw new Error(saveData.error || "Failed to save events to database.");
            }

        } catch (error) {
            console.error('Auto-Schedule Error:', error);
            alert('Failed to auto-schedule: ' + error.message);
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