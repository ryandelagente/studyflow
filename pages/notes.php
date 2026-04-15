<?php
// File: /pages/notes.php
// Purpose: A full-featured, dynamic page to manage user notes with a dedicated editor view.

// --- SETUP ---
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$error_message = '';
$success_message = '';
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

// --- VIEW ROUTING ---
$action = $_GET['action'] ?? 'list'; // 'list', 'create', 'edit'
$note_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$note_to_edit = null;

// --- DATABASE CONNECTION CHECK ---
if (!$link) {
    $error_message = "Database connection failed.";
}

// --- CRUD LOGIC ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify()) { $error_message = "Security token mismatch. Please try again."; goto render; }

    // LOGIC TO HANDLE SUBMISSIONS FROM SHARE WIZARD
    if (isset($_POST['add_note_from_share'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $note_type = $_POST['note_type'] ?? 'text';

        if (!empty($title) && !empty($content)) {
            // If it's a URL, format it as a clickable link
            if ($note_type === 'url') {
                $content = '<p>Shared Link: <a href="' . htmlspecialchars($content) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($content) . '</a></p>';
            }

            $sql = "INSERT INTO notes (user_id, tenant_id, title, content) VALUES (?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $title, $content);
                if (mysqli_stmt_execute($stmt)) {
                    $new_note_id = mysqli_insert_id($link);
                    header("Location: notes.php?action=edit&id=" . $new_note_id . "&success=1");
                    exit();
                }
            }
        }
        // If save fails, redirect back to share page with an error
        header("Location: share.php?error=1");
        exit();
    }


    // CREATE / UPDATE
    if (isset($_POST['save_note'])) {
        $note_id_to_save = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);

        if (!empty($title)) {
            if ($note_id_to_save > 0) { // This is an UPDATE
                $sql = "UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssiii", $title, $content, $note_id_to_save, $user_id, $tenant_id);
                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: notes.php?success=2");
                        exit();
                    } else { $error_message = "Error updating note."; }
                }
            } else { // This is a CREATE
                $sql = "INSERT INTO notes (user_id, tenant_id, title, content) VALUES (?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $title, $content);
                    if (mysqli_stmt_execute($stmt)) {
                        $new_note_id = mysqli_insert_id($link);
                        header("Location: notes.php?action=edit&id=" . $new_note_id . "&success=1"); // Redirect to edit the new note
                        exit();
                    } else { $error_message = "Error creating note."; }
                }
            }
        } else {
            $error_message = "Note title cannot be empty.";
        }
    }
    
    // DELETE (Can be triggered from a button on the edit page)
    if (isset($_POST['delete_note'])) {
        $note_id_to_delete = intval($_POST['note_id']);
        $sql = "DELETE FROM notes WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $note_id_to_delete, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: notes.php?success=3");
                exit();
            } else { $error_message = "Error deleting note."; }
        }
    }
}

if (isset($_GET['success'])) {
    $msgs = [1 => "Note created.", 2 => "Note updated.", 3 => "Note deleted."];
    $success_message = $msgs[(int)$_GET['success']] ?? '';
}
$success_message = $success_message ?: flash_get('success');
$error_message   = $error_message   ?: flash_get('error');

// --- READ DATA ---
$notes = [];
if ($link) {
    if (($action === 'edit' || $action === 'create') && $note_id > 0) {
        // Fetch a single note for the editor
        $sql = "SELECT id, title, content FROM notes WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $note_id, $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $note_to_edit = mysqli_fetch_assoc($result);
            if (!$note_to_edit && $action === 'edit') { // only redirect if it was an explicit edit request for a non-existent note
                header("Location: notes.php");
                exit();
            }
        }
    } elseif ($action === 'list') {
        // Fetch all notes for the list view
        $sql = "SELECT id, title, content, updated_at FROM notes WHERE user_id = ? AND tenant_id = ? ORDER BY updated_at DESC";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $notes[] = $row;
            }
        }
    }
}

// --- RENDER PAGE ---
render:
require_once(BASE_PATH . '/partials/header.php');
?>
<script src="https://cdn.tiny.cloud/1/0mgprpbs33v7zrkjsne3egwjvfuk9sd268xim5mrj8iz8npk/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea#note_content_editor',
    plugins: 'lists link image table code help wordcount advlist autoresize',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image | table | code',
    height: 500,
    menubar: false,
    content_style: 'body { font-family:Inter,sans-serif; font-size:14px }'
  });
</script>

<main class="flex-1 p-8 overflow-y-auto">
    <?php if ($action === 'create' || ($action === 'edit' && isset($note_to_edit))): ?>
    <form method="POST"><?php echo csrf_field(); ?>
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="note_id" id="note_id_field" value="<?php echo $note_to_edit['id']; ?>">
        <?php endif; ?>

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <a href="notes.php" class="text-gray-400 hover:text-purple-600">Document</a> / Manage your documents
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                 <button type="button" onclick="openModal('aiWriterModal')" class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-semibold text-sm">Ask AI to Write</button>
                 
                 <div class="relative inline-block text-left">
                    <button type="button" onclick="toggleDropdown('download-options')" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center">
                        Download
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="download-options" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-10">
                        <div class="py-1">
                            <a href="#" id="download-txt-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">as Text (.txt)</a>
                            <a href="#" id="download-html-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">as HTML (.html)</a>
                        </div>
                    </div>
                </div>

                 <button type="button" id="share-btn" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold text-sm">Share</button>
                 <button type="submit" name="save_note" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold">Save</button>
            </div>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-md">
            <input type="text" name="title" id="note-title-field" value="<?php echo htmlspecialchars($note_to_edit['title'] ?? 'Untitled Note'); ?>" placeholder="Note Title..." class="text-2xl font-bold w-full border-b pb-2 mb-6 focus:outline-none focus:border-purple-500">
            <textarea name="content" id="note_content_editor"><?php echo htmlspecialchars($note_to_edit['content'] ?? ''); ?></textarea>
        </div>
    </form>
    <?php else: ?>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold">Notes</h2>
            <p class="text-gray-500">Create, organize, and share your notes</p>
        </div>
        <a href="notes.php?action=create" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700">
            New Note
        </a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (empty($notes)): ?>
            <div class="col-span-full text-center text-gray-500 py-10">
                <p>You haven't created any notes yet. Click "New Note" to get started!</p>
            </div>
        <?php else: ?>
            <?php foreach ($notes as $note): ?>
            <a href="notes.php?action=edit&id=<?php echo $note['id']; ?>" class="bg-white p-6 rounded-lg shadow flex flex-col justify-between hover:shadow-lg transition-shadow">
                <div>
                    <h3 class="font-bold text-lg mb-2 truncate"><?php echo htmlspecialchars($note['title']); ?></h3>
                    <p class="text-sm text-gray-600 line-clamp-4">
                        <?php echo htmlspecialchars(strip_tags($note['content'])); // Strip HTML for plain text preview ?>
                    </p>
                </div>
                <p class="text-xs text-gray-500 mt-4">
                    Last updated: <?php echo date('M d, Y', strtotime($note['updated_at'])); ?>
                </p>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<div id="aiWriterModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Ask AI to Write</h2>
        <form id="ai-writer-form">
            <div class="mb-4">
                <label for="ai-prompt" class="block text-sm font-medium text-gray-700">Your Prompt</label>
                <textarea id="ai-prompt" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="e.g., Write a short summary about the industrial revolution..." required></textarea>
            </div>
            <div id="ai-error" class="hidden bg-red-100 text-red-700 px-4 py-2 rounded mb-4"></div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('aiWriterModal')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" id="ai-submit-btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg">
                    <span id="ai-button-text">Generate</span>
                    <span id="ai-loading" class="hidden">Generating...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

    // Dropdown toggle
    function toggleDropdown(dropdownId) {
        document.getElementById(dropdownId).classList.toggle('hidden');
    }
    // Close dropdown if clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.inline-flex')) {
            var dropdowns = document.getElementsByClassName("origin-top-right");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (!openDropdown.classList.contains('hidden')) {
                    openDropdown.classList.add('hidden');
                }
            }
        }
    }

    // --- DOWNLOAD & SHARE LOGIC ---
    if (document.getElementById('share-btn')) {
        const titleField = document.getElementById('note-title-field');
        
        // Download as TXT
        document.getElementById('download-txt-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const content = tinymce.activeEditor.getContent({ format: 'text' });
            const title = titleField.value.trim().replace(/\s+/g, '_') || 'note';
            downloadFile(title + '.txt', content, 'text/plain');
        });

        // Download as HTML
        document.getElementById('download-html-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const content = tinymce.activeEditor.getContent();
            const title = titleField.value.trim().replace(/\s+/g, '_') || 'note';
            downloadFile(title + '.html', content, 'text/html');
        });

        function downloadFile(filename, data, type) {
            const blob = new Blob([data], { type: type });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Share functionality
        document.getElementById('share-btn').addEventListener('click', async function() {
            const title = titleField.value;
            const text = tinymce.activeEditor.getContent({ format: 'text' });
            
            if (navigator.share) { // Web Share API (mobile)
                try {
                    await navigator.share({
                        title: title,
                        text: text,
                    });
                } catch (err) {
                    console.error('Share failed:', err.message);
                }
            } else { // Fallback for Desktop (Copy to Clipboard)
                try {
                    await navigator.clipboard.writeText(text);
                    alert('Note content copied to clipboard!');
                } catch (err) {
                    alert('Failed to copy content.');
                    console.error('Clipboard copy failed:', err.message);
                }
            }
        });
    }

    // AI Writer Logic
    const aiForm = document.getElementById('ai-writer-form');
    if(aiForm) {
        aiForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const prompt = document.getElementById('ai-prompt').value;
            const submitBtn = document.getElementById('ai-submit-btn');
            const btnText = document.getElementById('ai-button-text');
            const loadingText = document.getElementById('ai-loading');
            const errorDiv = document.getElementById('ai-error');
            
            btnText.classList.add('hidden');
            loadingText.classList.remove('hidden');
            submitBtn.disabled = true;
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/ai-assist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
                    body: JSON.stringify({ feature: 'notes_write', context: prompt })
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                if (tinymce.activeEditor) {
                    tinymce.activeEditor.insertContent(data.result);
                }
                
                closeModal('aiWriterModal');
                document.getElementById('ai-prompt').value = '';

            } catch (error) {
                console.error('AI Writer Error:', error);
                errorDiv.textContent = 'Error: ' + error.message;
                errorDiv.classList.remove('hidden');
            } finally {
                btnText.classList.remove('hidden');
                loadingText.classList.add('hidden');
                submitBtn.disabled = false;
            }
        });
    }
</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>