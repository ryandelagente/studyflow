<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/resources.php
// Purpose: A full-featured page to manage, upload, view, and analyze user files with AI.

require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$error_message = '';
$success_message = '';
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session

// --- SAAS FIX: Define a tenant-specific upload directory ---
$upload_dir = BASE_PATH . '/uploads/' . $tenant_id . '/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// --- VIEW ROUTING ---
$action = $_GET['action'] ?? 'list'; // 'list' or 'view'
$view_file = null;

if ($action === 'view' && isset($_GET['file'])) {
    // Sanitize filename to prevent directory traversal
    $file_name = basename($_GET['file']);
    $file_path = $upload_dir . $file_name;
    
    if (file_exists($file_path)) {
        $view_file = [
            'name' => $file_name,
            'url' => BASE_URL . '/uploads/' . $tenant_id . '/' . $file_name,
            'path' => $file_path,
            'extension' => strtolower(pathinfo($file_name, PATHINFO_EXTENSION))
        ];
    } else {
        // If file not found, redirect back to the list
        header("Location: resources.php");
        exit();
    }
}


// --- POST LOGIC (Upload/Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // HANDLE FILE UPLOAD
    if (isset($_POST['upload_file'])) {
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
            $safe_file_name = preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES['resource_file']['name']));
            $target_path = $upload_dir . $safe_file_name;
            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $target_path)) {
                header("Location: resources.php?success=1");
                exit();
            } else { $error_message = "Error moving the uploaded file."; }
        } else { $error_message = "No file was uploaded or an error occurred."; }
    }
    // HANDLE FILE DELETION
    if (isset($_POST['delete_file'])) {
        $safe_file_to_delete = basename(trim($_POST['file_name']));
        $file_path = $upload_dir . $safe_file_to_delete;
        if (file_exists($file_path) && unlink($file_path)) {
            header("Location: resources.php?success=2");
            exit();
        } else { $error_message = "File not found or could not be deleted."; }
    }
}

// Handle success messages
if (isset($_GET['success'])) { 
    switch($_GET['success']){
        case 1: $success_message = "File uploaded successfully."; break;
        case 2: $success_message = "File deleted successfully."; break;
    }
}

// --- READ: Scan the tenant's directory to get the list of files ---
$resources = [];
if ($action === 'list') {
    if(is_dir($upload_dir)){
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        foreach ($files as $file) {
            $resources[] = [
                'name' => $file,
                'owner' => $_SESSION["username"],
                'created' => filemtime($upload_dir . $file)
            ];
        }
    }
}

// Allowed extensions for AI Text Analysis
$ai_supported_formats = ['txt', 'md', 'csv', 'html', 'json', 'xml'];

require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">

    <?php if ($action === 'view' && isset($view_file)): ?>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">
            <a href="resources.php" class="text-gray-400 hover:text-purple-600">Resources</a> / View
        </h2>
    </div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($view_file['name']); ?></h2>
        </div>
        <div class="flex items-center space-x-2">
             <a href="resources.php" class="bg-gray-200 text-gray-800 px-5 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-colors">Back</a>
             <a href="<?php echo htmlspecialchars($view_file['url']); ?>" download class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">Download</a>
        </div>
    </div>

    <?php if (in_array($view_file['extension'], $ai_supported_formats)): ?>
        <?php $file_content = htmlspecialchars(file_get_contents($view_file['path'])); ?>
        
        <div class="mb-6 flex flex-col lg:flex-row gap-4 bg-gradient-to-r from-purple-50 to-indigo-50 p-6 rounded-xl border border-purple-100 shadow-sm">
            <div class="lg:w-1/3 flex flex-col justify-center">
                <h4 class="font-bold text-purple-900 text-lg mb-2">✨ AI Document Analysis</h4>
                <p class="text-sm text-purple-700 mb-4">Let AI read this document for you. Extract the main points or generate searchable tags instantly.</p>
                <div class="flex flex-col space-y-2">
                    <button onclick="analyzeDocument('summarize')" id="btn-summarize" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                        📄 Summarize Document
                    </button>
                    <button onclick="analyzeDocument('tag')" id="btn-tag" class="bg-white border border-purple-200 text-purple-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-50 transition-colors shadow-sm">
                        🏷️ Auto-Tag Document
                    </button>
                </div>
            </div>
            
            <div id="ai-results-panel" class="hidden lg:w-2/3 bg-white p-5 rounded-lg shadow-sm border border-purple-100 text-sm text-gray-700 overflow-y-auto max-h-80">
                </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 overflow-x-auto">
            <h4 class="text-gray-500 font-semibold text-xs uppercase tracking-wider mb-4 border-b pb-2">Document Content</h4>
            <pre id="document-content" class="text-sm text-gray-800 whitespace-pre-wrap font-sans"><?php echo $file_content; ?></pre>
        </div>

    <?php elseif (in_array($view_file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200">
            <img src="<?php echo htmlspecialchars($view_file['url']); ?>" alt="<?php echo htmlspecialchars($view_file['name']); ?>" class="max-w-full h-auto mx-auto rounded">
        </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200 text-center py-20">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <i data-lucide="file" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No Preview Available</h3>
            <p class="text-gray-500 mb-6">We cannot generate a preview or AI analysis for <strong>.<?php echo htmlspecialchars($view_file['extension']); ?></strong> files yet.</p>
            <a href="<?php echo htmlspecialchars($view_file['url']); ?>" download class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors inline-block">Download File</a>
        </div>
    <?php endif; ?>


    <?php else: ?>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold">Resource Management</h2>
            <p class="text-gray-500">Upload study materials, syllabi, and documents.</p>
        </div>
        <button onclick="openModal('uploadModal')" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
            Upload File
        </button>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
         <div class="flex justify-between items-center mb-4">
            <div class="flex items-center text-sm text-gray-600">
                <span>Show</span>
                <select class="mx-2 border rounded-md p-1.5 focus:ring-purple-500 focus:border-purple-500"><option>10</option></select>
                <span>entries</span>
            </div>
            <div class="w-1/3"><input type="text" placeholder="Search files..." class="border rounded-md p-2 w-full focus:ring-purple-500 focus:border-purple-500"></div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="p-2 font-semibold">FILE NAME</th>
                    <th class="p-2 font-semibold">OWNER</th>
                    <th class="p-2 font-semibold">UPLOADED</th>
                    <th class="p-2 font-semibold text-right">MANAGE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resources)): ?>
                    <tr><td colspan="4" class="text-center p-12 text-gray-500">No resources found. Click "Upload File" to add some.</td></tr>
                <?php else: ?>
                    <?php foreach ($resources as $resource): ?>
                        <tr class="border-t hover:bg-gray-50 transition-colors">
                            <td class="p-3 font-medium">
                                <a href="resources.php?action=view&file=<?php echo urlencode($resource['name']); ?>" class="flex items-center text-purple-600 hover:text-purple-800 transition-colors">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2 text-gray-400"></i>
                                    <?php echo htmlspecialchars($resource['name']); ?>
                                </a>
                            </td>
                            <td class="p-3 text-gray-600"><?php echo htmlspecialchars($resource['owner']); ?></td>
                            <td class="p-3 text-gray-600"><?php echo date('M d, Y', $resource['created']); ?></td>
                            <td class="p-3 text-right">
                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($resource['name']); ?>">
                                    <button type="submit" name="delete_file" class="text-gray-400 hover:text-red-500 p-1 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
         <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
            <div>Showing 1 to <?php echo count($resources); ?> of <?php echo count($resources); ?> entries</div>
            <div class="flex items-center">
                <a href="#" class="px-3 py-1 border rounded-l-md hover:bg-gray-100">Previous</a>
                <a href="#" class="px-3 py-1 border bg-purple-600 text-white hover:bg-purple-700">1</a>
                <a href="#" class="px-3 py-1 border rounded-r-md hover:bg-gray-100">Next</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-2">Upload New Resource</h2>
        <p class="text-gray-500 text-sm mb-6">Upload .txt, .md, .csv, or image files. Text files can be analyzed by AI.</p>
        <form method="POST" action="resources.php" enctype="multipart/form-data">
            <div class="mb-6 border-2 border-dashed border-purple-200 bg-purple-50 p-6 rounded-lg text-center">
                <i data-lucide="upload-cloud" class="w-10 h-10 text-purple-400 mx-auto mb-2"></i>
                <input type="file" name="resource_file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('uploadModal')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="upload_file" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

    // --- AI DOCUMENT ANALYSIS SCRIPT ---
    async function analyzeDocument(mode) {
        const contentEl = document.getElementById('document-content');
        const resultPanel = document.getElementById('ai-results-panel');
        const btn = mode === 'summarize' ? document.getElementById('btn-summarize') : document.getElementById('btn-tag');
        
        if (!contentEl) return;
        const content = contentEl.textContent;
        const originalText = btn.innerHTML;

        if (!content || content.trim() === '') {
            alert('Document is empty.');
            return;
        }

        btn.innerHTML = "⏳ Analyzing...";
        btn.disabled = true;
        resultPanel.classList.remove('hidden');
        resultPanel.innerHTML = '<div class="flex items-center justify-center h-full space-x-2 text-purple-600"><i data-lucide="loader" class="w-5 h-5 animate-spin"></i><span>AI is reading the document...</span></div>';
        lucide.createIcons();

        try {
            let prompt = "";
            // We limit content to ~15,000 characters to safely fit inside the API context window without breaking limits
            const safeContent = content.substring(0, 15000); 

            if (mode === 'summarize') {
                prompt = `You are a helpful study assistant. Please provide a structured summary of the following document. Extract the main points, key terms, and any deadlines/important dates mentioned. Keep it well-formatted with markdown bullet points. \n\nDocument Content:\n${safeContent}`;
            } else {
                prompt = `You are a helpful study assistant organizing files. Read the following document and suggest 3 to 6 relevant tags or categories that describe its content perfectly. Return ONLY a comma-separated list of tags (e.g., Biology, Final Exam, Chapter 4), with no extra text or markdown formatting. \n\nDocument Content:\n${safeContent}`;
            }

            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            if (mode === 'summarize') {
                // Format the summary nicely
                resultPanel.innerHTML = `
                    <div class="flex items-center justify-between mb-3 border-b pb-2">
                        <h5 class="font-bold text-purple-900 flex items-center"><span class="text-xl mr-2">📄</span> Document Summary</h5>
                        <button onclick="document.getElementById('ai-results-panel').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <div class="whitespace-pre-wrap leading-relaxed">${data.reply}</div>
                `;
            } else {
                // Parse tags and render as pills
                const tags = data.reply.split(',').map(t => t.trim().replace(/[^a-zA-Z0-9 -]/g, '')).filter(t => t);
                let tagsHtml = `
                    <div class="flex items-center justify-between mb-3 border-b pb-2">
                        <h5 class="font-bold text-indigo-900 flex items-center"><span class="text-xl mr-2">🏷️</span> Auto-Generated Tags</h5>
                        <button onclick="document.getElementById('ai-results-panel').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">The AI suggests categorizing this file with the following tags:</p>
                    <div class="flex flex-wrap gap-2">
                `;
                tags.forEach(t => {
                    tagsHtml += `<span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold border border-indigo-200 shadow-sm">${t}</span>`;
                });
                tagsHtml += `</div>`;
                resultPanel.innerHTML = tagsHtml;
            }

        } catch (error) {
            console.error('AI Analysis Error:', error);
            resultPanel.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-md border border-red-200"><strong>Analysis Failed:</strong> ${error.message}</div>`;
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