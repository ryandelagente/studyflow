<?php
// /pages/calendar.php

// Use an absolute path for reliable file inclusion
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

// Initialize variables
$db_error = null;
$success_message = null;
$events = [];

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
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!csrf_verify()) { $db_error = "Security token mismatch. Please try again."; goto render; }
        // CREATE, UPDATE, DELETE logic remains the same
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
            $sql = "UPDATE events SET title = ?, description = ?, start_time = ?, end_time = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssssiii", $title, $description, $start_time, $end_time, $event_id, $user_id, $tenant_id);
                if (mysqli_stmt_execute($stmt)) { header("Location: calendar.php?success=2&week=".$start_time); exit(); } 
                else { $db_error = "Error updating event: " . mysqli_stmt_error($stmt); }
                mysqli_stmt_close($stmt);
            }
        }
        // DELETE
        if (isset($_POST['delete_event'])) {
            $event_id = $_POST['event_id'];
            $sql = "DELETE FROM events WHERE id = ? AND user_id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $event_id, $user_id, $tenant_id);
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

}

render:
// --- READ (Fetch events for the current week) ---
if (isset($link) && $link) {
    $sql = "SELECT id, title, description, start_time, end_time FROM events WHERE user_id = ? AND tenant_id = ? AND start_time <= ? AND end_time >= ? ORDER BY start_time ASC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $week_end_str, $week_start_str);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $events[] = $row; }
            mysqli_free_result($result);
        } else { $db_error = "Error fetching events: " . mysqli_error($link); }
        mysqli_stmt_close($stmt);
    }
}
require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
         <div>
            <h2 class="text-3xl font-bold">Calendar</h2>
        </div>
        <button onclick="openModal('add-event-modal')" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">Add Event</button>
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
                    ?>
                    <div class="absolute w-full px-1" style="grid-column: <?php echo $col; ?>; top: <?php echo $top_offset; ?>px;">
                         <div class="bg-purple-500 text-white rounded p-1 text-xs overflow-hidden cursor-pointer edit-event-btn hover:bg-purple-600"
                              style="height: <?php echo $height; ?>px;"
                              data-id="<?php echo $event['id']; ?>"
                              data-title="<?php echo htmlspecialchars($event['title']); ?>"
                              data-description="<?php echo htmlspecialchars($event['description']); ?>"
                              data-start-time="<?php echo date('Y-m-d\TH:i', strtotime($event['start_time'])); ?>"
                              data-end-time="<?php echo date('Y-m-d\TH:i', strtotime($event['end_time'])); ?>">
                            <p class="font-bold"><?php echo htmlspecialchars($event['title']); ?></p>
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
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium">Start Time</label>
                    <input type="datetime-local" name="start_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">End Time</label>
                    <input type="datetime-local" name="end_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('add-event-modal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="add_event" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Add Event</button>
            </div>
        </form>
    </div>
</div>
<div id="edit-event-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-2xl font-bold mb-6">Edit Event</h3>
        <form action="calendar.php?week=<?php echo $target_date->format('Y-m-d'); ?>" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="event_id" id="edit_event_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" id="edit_title" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" id="edit_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium">Start Time</label>
                    <input type="datetime-local" name="start_time" id="edit_start_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">End Time</label>
                    <input type="datetime-local" name="end_time" id="edit_end_time" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
            </div>
            <div class="flex justify-between items-center">
                 <button type="submit" name="delete_event" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this event?');">Delete Event</button>
                <div class="flex space-x-4">
                    <button type="button" onclick="closeModal('edit-event-modal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" name="edit_event" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
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
    });
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>