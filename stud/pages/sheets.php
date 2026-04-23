<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/sheets.php
// Purpose: Manage spreadsheets with AI Formula Assistant and Data Summarizer.

require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"]; 
$action = $_GET['action'] ?? 'list';
$sheet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sheet_to_edit = null;
$error_message = '';
$success_message = '';

// --- DATABASE CONNECTION CHECK ---
if (!$link) {
    $error_message = "Database connection failed.";
}

// --- CRUD LOGIC ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST") {
    // CREATE
    if (isset($_POST['add_sheet'])) {
        $title = "Untitled Spreadsheet";
        $default_content = json_encode(array_fill(0, 50, array_fill(0, 20, '')));
        $sql = "INSERT INTO spreadsheets (user_id, tenant_id, title, content) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $title, $default_content);
            if (mysqli_stmt_execute($stmt)) {
                $new_sheet_id = mysqli_insert_id($link);
                header("Location: sheets.php?action=edit&id=" . $new_sheet_id);
                exit();
            }
        }
    }
    // UPDATE
    if (isset($_POST['save_sheet'])) {
        $sheet_id_to_save = intval($_POST['sheet_id']);
        $title = trim($_POST['title']);
        $content = $_POST['sheet_content']; 
        if (!empty($sheet_id_to_save) && !empty($title)) {
            $sql = "UPDATE spreadsheets SET title = ?, content = ? WHERE id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $sheet_id_to_save, $tenant_id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: sheets.php?action=edit&id=" . $sheet_id_to_save . "&success=2");
                    exit();
                }
            }
        }
    }
    // DELETE
    if (isset($_POST['delete_sheet'])) {
        $sheet_id_to_delete = intval($_POST['sheet_id']);
        $sql = "DELETE FROM spreadsheets WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $sheet_id_to_delete, $tenant_id);
            if(mysqli_stmt_execute($stmt)) {
                header("Location: sheets.php?success=3");
                exit();
            } else {
                $error_message = "Error deleting spreadsheet.";
            }
        }
    }
}

// Handle success messages
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case 1: $success_message = "Spreadsheet created successfully."; break;
        case 2: $success_message = "Spreadsheet saved successfully."; break;
        case 3: $success_message = "Spreadsheet deleted successfully."; break;
    }
}

// --- READ DATA ---
$spreadsheets = [];
if ($link) {
    if ($action === 'edit' && $sheet_id > 0) {
        $sql = "SELECT id, title, content FROM spreadsheets WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $sheet_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $sheet_to_edit = mysqli_fetch_assoc($result);
            if (!$sheet_to_edit) { header("Location: sheets.php"); exit(); }
        }
    } else {
        $sql = "SELECT id, title, updated_at FROM spreadsheets WHERE tenant_id = ? ORDER BY updated_at DESC";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $spreadsheets[] = $row; }
        }
    }
}

require_once(BASE_PATH . '/partials/header.php');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<main class="flex-1 p-8 overflow-y-auto">
    <?php if ($action === 'edit' && isset($sheet_to_edit)): ?>
    <form method="POST" id="sheet-form">
        <input type="hidden" name="sheet_id" value="<?php echo $sheet_to_edit['id']; ?>">
        <input type="hidden" name="sheet_content" id="sheet_content_hidden">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <a href="sheets.php" class="text-gray-400 hover:text-purple-600">Spreadsheet</a> / Edit
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                 <button type="button" id="download-csv-btn" class="bg-purple-100 text-purple-800 px-4 py-2 rounded-lg font-semibold text-sm">Download CSV</button>
                 <button type="submit" name="save_sheet" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold shadow-sm hover:bg-purple-700">Save</button>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4 bg-white p-4 rounded-lg shadow-sm border border-purple-100">
            <div>
                <label class="block text-xs font-bold text-purple-600 uppercase mb-2">🪄 Formula Assistant</label>
                <div class="flex space-x-2">
                    <input type="text" id="ai-formula-input" placeholder="e.g., Average of Col B if Col A is 'Passed'..." class="flex-1 border border-gray-200 rounded-md p-2 text-sm focus:ring-1 focus:ring-purple-500 outline-none">
                    <button type="button" onclick="getAIFormula()" id="btn-formula" class="bg-purple-600 text-white px-4 py-2 rounded-md text-xs font-bold hover:bg-purple-700 transition-colors">Get Formula</button>
                </div>
                <div id="formula-result" class="mt-2 text-xs font-mono text-gray-600 hidden bg-gray-50 p-2 rounded border border-dashed border-gray-300"></div>
            </div>
            <div>
                <label class="block text-xs font-bold text-indigo-600 uppercase mb-2">📊 Data Summarizer</label>
                <button type="button" onclick="summarizeData()" id="btn-summarize" class="w-full bg-indigo-50 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-md text-xs font-bold hover:bg-indigo-100 transition-colors">
                    Summarize Selected Range
                </button>
                <div id="summary-result" class="mt-2 text-xs text-gray-700 hidden bg-indigo-50/30 p-2 rounded border border-indigo-100 italic"></div>
            </div>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
            <input type="text" name="title" id="sheet-title-field" value="<?php echo htmlspecialchars($sheet_to_edit['title']); ?>" class="text-xl font-semibold w-full border-b pb-2 mb-4 focus:outline-none focus:border-purple-500">
            
            <div class="flex items-center space-x-2 border-b p-2 mb-2 bg-gray-50 rounded-t-md">
                <button type="button" id="bold-btn" class="p-2 rounded hover:bg-gray-200" title="Bold"><i data-lucide="bold" class="w-4 h-4"></i></button>
                <button type="button" id="italic-btn" class="p-2 rounded hover:bg-gray-200" title="Italic"><i data-lucide="italic" class="w-4 h-4"></i></button>
                <div class="border-l h-6 mx-2"></div>
                <button type="button" id="align-left-btn" class="p-2 rounded hover:bg-gray-200" title="Align Left"><i data-lucide="align-left" class="w-4 h-4"></i></button>
                <button type="button" id="align-center-btn" class="p-2 rounded hover:bg-gray-200" title="Align Center"><i data-lucide="align-center" class="w-4 h-4"></i></button>
                <button type="button" id="align-right-btn" class="p-2 rounded hover:bg-gray-200" title="Align Right"><i data-lucide="align-right" class="w-4 h-4"></i></button>
            </div>

            <div id="spreadsheet-editor"></div>
        </div>
    </form>
    
    <?php else: ?>
    <div class="flex justify-between items-center mb-8">
        <div><h2 class="text-3xl font-bold text-gray-800">Spreadsheets</h2></div>
        <form method="POST" action="sheets.php">
             <button type="submit" name="add_sheet" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">New Spreadsheet</button>
        </form>
    </div>
     <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="p-2 font-semibold">TITLE</th>
                    <th class="p-2 font-semibold">LAST UPDATED</th>
                    <th class="p-2 font-semibold text-right">MANAGE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($spreadsheets)): ?>
                    <tr><td colspan="3" class="text-center p-12 text-gray-500">No spreadsheets found. Create one to begin.</td></tr>
                <?php else: ?>
                    <?php foreach ($spreadsheets as $sheet): ?>
                        <tr class="border-t hover:bg-gray-50 transition-colors">
                            <td class="p-3 font-medium text-gray-800">
                                <a href="sheets.php?action=edit&id=<?php echo $sheet['id']; ?>" class="flex items-center hover:text-purple-600">
                                    <i data-lucide="table-2" class="inline-block w-5 h-5 mr-3 text-green-600"></i>
                                    <?php echo htmlspecialchars($sheet['title']); ?>
                                </a>
                            </td>
                            <td class="p-3 text-gray-600"><?php echo date('M d, Y, g:i a', strtotime($sheet['updated_at'])); ?></td>
                            <td class="p-3 text-right">
                                <form method="POST" action="sheets.php" onsubmit="return confirm('Delete this spreadsheet?');" class="inline-block">
                                    <input type="hidden" name="sheet_id" value="<?php echo $sheet['id']; ?>">
                                    <button type="submit" name="delete_sheet" class="text-gray-400 hover:text-red-500 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>


<?php require_once(BASE_PATH . '/partials/footer.php'); ?>

<?php if ($action === 'edit' && isset($sheet_to_edit)): ?>
<script>
    let hot;
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('spreadsheet-editor');
        const sheetContentInput = document.getElementById('sheet_content_hidden');
        const sheetForm = document.getElementById('sheet-form');
        let sheetData = [];
        try {
            sheetData = JSON.parse(<?php echo json_encode($sheet_to_edit['content']); ?>) || [];
        } catch(e) {
            sheetData = Array(50).fill().map(() => Array(20).fill(''));
        }

        hot = new Handsontable(container, {
            data: sheetData,
            rowHeaders: true,
            colHeaders: true,
            height: '60vh',
            licenseKey: 'non-commercial-and-evaluation',
            stretchH: 'all',
            contextMenu: true,
            dropdownMenu: true,
            filters: true,
            manualColumnResize: true,
            manualRowResize: true,
            customBorders: true
        });
        
        function applyStyle(styleKey, styleValue) {
            const selected = hot.getSelected();
            if (!selected) return;
            selected.forEach(([startRow, startCol, endRow, endCol]) => {
                for (let r = startRow; r <= endRow; r++) {
                    for (let c = startCol; c <= endCol; c++) {
                        let currentClassName = hot.getCellMeta(r, c, 'className') || '';
                        if (styleKey === 'align') {
                            currentClassName = currentClassName.replace(/htLeft|htCenter|htRight/g, '').trim();
                            currentClassName += ` ${styleValue}`;
                        } else if (styleKey === 'font-weight') {
                            currentClassName = currentClassName.includes('htBold') ? currentClassName.replace('htBold', '') : currentClassName + ' htBold';
                        } else if (styleKey === 'font-style') {
                            currentClassName = currentClassName.includes('htItalic') ? currentClassName.replace('htItalic', '') : currentClassName + ' htItalic';
                        }
                        hot.setCellMeta(r, c, 'className', currentClassName.trim());
                    }
                }
            });
            hot.render();
        }
        
        document.getElementById('bold-btn').addEventListener('click', () => applyStyle('font-weight', 'htBold'));
        document.getElementById('italic-btn').addEventListener('click', () => applyStyle('font-style', 'htItalic'));
        document.getElementById('align-left-btn').addEventListener('click', () => applyStyle('align', 'htLeft'));
        document.getElementById('align-center-btn').addEventListener('click', () => applyStyle('align', 'htCenter'));
        document.getElementById('align-right-btn').addEventListener('click', () => applyStyle('align', 'htRight'));

        sheetForm.addEventListener('submit', () => {
            sheetContentInput.value = JSON.stringify(hot.getData());
        });
    });

    // --- AI FORMULA ASSISTANT ---
    async function getAIFormula() {
        const input = document.getElementById('ai-formula-input').value.trim();
        const resDiv = document.getElementById('formula-result');
        const btn = document.getElementById('btn-formula');
        if (!input) return alert("Please type what you want to calculate.");

        btn.disabled = true; btn.innerHTML = "⏳...";
        resDiv.classList.add('hidden');

        try {
            const prompt = `You are a spreadsheet expert. Convert the following plain English request into a standard Excel/Google Sheets formula: "${input}". 
            Respond ONLY with the formula string starting with '=', no explanation.`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            resDiv.textContent = "Suggested Formula: " + data.reply.trim();
            resDiv.classList.remove('hidden');
        } catch (e) {
            alert("AI Formula error: " + e.message);
        } finally {
            btn.disabled = false; btn.innerHTML = "Get Formula";
        }
    }

    // --- AI DATA SUMMARIZER ---
    async function summarizeData() {
        const selected = hot.getSelected();
        const resDiv = document.getElementById('summary-result');
        const btn = document.getElementById('btn-summarize');
        
        if (!selected) return alert("Please select a range of data cells to summarize.");

        const dataRows = [];
        selected.forEach(([sr, sc, er, ec]) => {
            for (let r = Math.min(sr, er); r <= Math.max(sr, er); r++) {
                let row = [];
                for (let c = Math.min(sc, ec); c <= Math.max(sc, ec); c++) {
                    row.push(hot.getDataAtCell(r, c));
                }
                dataRows.push(row.join(', '));
            }
        });

        if (dataRows.length === 0) return;

        btn.disabled = true; btn.innerHTML = "⏳ Analyzing...";
        resDiv.classList.add('hidden');

        try {
            const prompt = `Analyze this range of spreadsheet data and provide a concise 2-sentence summary of the trends, averages, or notable patterns you see: \n${dataRows.join('\n')}`;
            
            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            resDiv.textContent = "AI Analysis: " + data.reply.trim();
            resDiv.classList.remove('hidden');
        } catch (e) {
            alert("AI Summary error: " + e.message);
        } finally {
            btn.disabled = false; btn.innerHTML = "Summarize Selected Range";
        }
    }
</script>
<?php endif; ?>