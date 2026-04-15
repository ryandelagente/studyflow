<?php
// File: /pages/sheets.php
// Purpose: A full-featured, dynamic page to manage spreadsheets with a dedicated editor view and custom toolbar.

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
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
    if (!csrf_verify()) { $error_message = "Security token mismatch. Please try again."; goto render; }
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
            $sql = "UPDATE spreadsheets SET title = ?, content = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssiii", $title, $content, $sheet_id_to_save, $user_id, $tenant_id);
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
        $sql = "DELETE FROM spreadsheets WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $sheet_id_to_delete, $user_id, $tenant_id);
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

render:
// --- READ DATA ---
$spreadsheets = [];
if ($link) {
    if ($action === 'edit' && $sheet_id > 0) {
        $sql = "SELECT id, title, content FROM spreadsheets WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $sheet_id, $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $sheet_to_edit = mysqli_fetch_assoc($result);
            if (!$sheet_to_edit) { header("Location: sheets.php"); exit(); }
        }
    } else {
        $sql = "SELECT id, title, updated_at FROM spreadsheets WHERE user_id = ? AND tenant_id = ? ORDER BY updated_at DESC";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
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
    <form method="POST" id="sheet-form"><?php echo csrf_field(); ?>
        <input type="hidden" name="sheet_id" value="<?php echo $sheet_to_edit['id']; ?>">
        <input type="hidden" name="sheet_content" id="sheet_content_hidden">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <a href="sheets.php" class="text-gray-400 hover:text-purple-600">Document</a> / Manage your documents
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                 <button type="button" id="download-csv-btn" class="bg-purple-100 text-purple-800 px-4 py-2 rounded-lg font-semibold text-sm">Download CSV</button>
                 <button type="button" id="share-btn" class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-semibold text-sm">Share</button>
                 <button type="submit" name="save_sheet" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold">Save</button>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-lg shadow-md">
            <input type="text" name="title" id="sheet-title-field" value="<?php echo htmlspecialchars($sheet_to_edit['title']); ?>" class="text-xl font-semibold w-full border-b pb-2 mb-4 focus:outline-none focus:border-purple-500">
            
            <div class="flex items-center space-x-2 border-b p-2 mb-2 bg-gray-50 rounded-t-md">
                <button type="button" id="bold-btn" class="p-2 rounded hover:bg-gray-200" title="Bold"><i data-lucide="bold" class="w-4 h-4"></i></button>
                <button type="button" id="italic-btn" class="p-2 rounded hover:bg-gray-200" title="Italic"><i data-lucide="italic" class="w-4 h-4"></i></button>
                <button type="button" id="underline-btn" class="p-2 rounded hover:bg-gray-200" title="Underline"><i data-lucide="underline" class="w-4 h-4"></i></button>
                <div class="border-l h-6 mx-2"></div>
                <button type="button" id="align-left-btn" class="p-2 rounded hover:bg-gray-200" title="Align Left"><i data-lucide="align-left" class="w-4 h-4"></i></button>
                <button type="button" id="align-center-btn" class="p-2 rounded hover:bg-gray-200" title="Align Center"><i data-lucide="align-center" class="w-4 h-4"></i></button>
                <button type="button" id="align-right-btn" class="p-2 rounded hover:bg-gray-200" title="Align Right"><i data-lucide="align-right" class="w-4 h-4"></i></button>
                 <div class="border-l h-6 mx-2"></div>
                <label for="color-btn" class="p-2 rounded hover:bg-gray-200 cursor-pointer" title="Text Color"><i data-lucide="highlighter" class="w-4 h-4"></i></label>
                <input type="color" id="color-btn" class="w-0 h-0 opacity-0">
            </div>

            <div id="spreadsheet-editor"></div>
        </div>
    </form>
    
    <?php else: ?>
    <div class="flex justify-between items-center mb-8">
        <div><h2 class="text-2xl font-bold">Spreadsheets</h2></div>
        <form method="POST" action="sheets.php">
             <button type="submit" name="add_sheet" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">New Spreadsheet</button>
        </form>
    </div>
     <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
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
                    <tr><td colspan="3" class="text-center p-8 text-gray-500">No spreadsheets found.</td></tr>
                <?php else: ?>
                    <?php foreach ($spreadsheets as $sheet): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800">
                                <a href="sheets.php?action=edit&id=<?php echo $sheet['id']; ?>" class="flex items-center hover:text-purple-600">
                                    <i data-lucide="table-2" class="inline-block w-5 h-5 mr-3 text-green-600"></i>
                                    <?php echo htmlspecialchars($sheet['title']); ?>
                                </a>
                            </td>
                            <td class="p-3 text-gray-600"><?php echo date('M d, Y, g:i a', strtotime($sheet['updated_at'])); ?></td>
                            <td class="p-3 text-right">
                                <div class="relative inline-block text-left">
                                    <button onclick="toggleDropdown('menu-<?php echo $sheet['id']; ?>')" class="text-gray-400 hover:text-blue-500 p-1">
                                        <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                                    </button>
                                    <div id="menu-<?php echo $sheet['id']; ?>" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-10">
                                        <div class="py-1">
                                            <a href="sheets.php?action=edit&id=<?php echo $sheet['id']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                                            <form method="POST" action="sheets.php" onsubmit="return confirm('Are you sure you want to delete this spreadsheet?');" class="w-full">
                                                <input type="hidden" name="sheet_id" value="<?php echo $sheet['id']; ?>">
                                                <button type="submit" name="delete_sheet" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('spreadsheet-editor');
        const sheetContentInput = document.getElementById('sheet_content_hidden');
        const sheetForm = document.getElementById('sheet-form');
        const titleField = document.getElementById('sheet-title-field');
        let sheetData = [];
        try {
            sheetData = JSON.parse(<?php echo json_encode($sheet_to_edit['content']); ?>) || [];
        } catch(e) {
            sheetData = Array(50).fill().map(() => Array(20).fill(''));
        }

        const hot = new Handsontable(container, {
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
            customBorders: true,
            renderer: function(instance, td, row, col, prop, value, cellProperties) {
                Handsontable.renderers.TextRenderer.apply(this, arguments);
                if (cellProperties.className) {
                    td.className += ' ' + cellProperties.className;
                }
                if (cellProperties.color) {
                    td.style.color = cellProperties.color;
                }
            }
        });
        
        function applyStyle(styleKey, styleValue) {
            const selected = hot.getSelected();
            if (!selected) return;

            selected.forEach(([startRow, startCol, endRow, endCol]) => {
                for (let r = startRow; r <= endRow; r++) {
                    for (let c = startCol; c <= endCol; c++) {
                        const currentClassName = hot.getCellMeta(r, c, 'className') || '';
                        let newClassName = currentClassName;
                        
                        if (styleKey === 'align') {
                            newClassName = newClassName.replace(/htLeft|htCenter|htRight|htJustify/g, '').trim();
                            newClassName += ` ${styleValue}`;
                        } else if (styleKey === 'font-weight') {
                            newClassName = newClassName.includes('htBold') ? newClassName.replace('htBold', '') : newClassName + ' htBold';
                        } else if (styleKey === 'font-style') {
                            newClassName = newClassName.includes('htItalic') ? newClassName.replace('htItalic', '') : newClassName + ' htItalic';
                        } else if (styleKey === 'text-decoration') {
                             newClassName = newClassName.includes('htUnderline') ? newClassName.replace('htUnderline', '') : newClassName + ' htUnderline';
                        }
                        
                        hot.setCellMeta(r, c, 'className', newClassName.trim());
                    }
                }
            });
            hot.render();
        }
        
        document.getElementById('bold-btn').addEventListener('click', () => applyStyle('font-weight', 'htBold'));
        document.getElementById('italic-btn').addEventListener('click', () => applyStyle('font-style', 'htItalic'));
        document.getElementById('underline-btn').addEventListener('click', () => applyStyle('text-decoration', 'htUnderline'));
        document.getElementById('align-left-btn').addEventListener('click', () => applyStyle('align', 'htLeft'));
        document.getElementById('align-center-btn').addEventListener('click', () => applyStyle('align', 'htCenter'));
        document.getElementById('align-right-btn').addEventListener('click', () => applyStyle('align', 'htRight'));
        
        document.getElementById('color-btn').addEventListener('input', (e) => {
            const selected = hot.getSelected();
            if (!selected) return;
            const color = e.target.value;
            selected.forEach(([startRow, startCol, endRow, endCol]) => {
                for (let r = startRow; r <= endRow; r++) {
                    for (let c = startCol; c <= endCol; c++) {
                        hot.setCellMeta(r, c, 'color', color);
                    }
                }
            });
            hot.render();
        });

        sheetForm.addEventListener('submit', function() {
            sheetContentInput.value = JSON.stringify(hot.getData());
        });
        
        // --- DOWNLOAD & SHARE LOGIC ---
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

        document.getElementById('download-csv-btn').addEventListener('click', function() {
            const data = hot.getSourceData();
            const title = titleField.value.trim().replace(/\s+/g, '_') || 'spreadsheet';
            const csvContent = data.map(row => 
                row.map(cell => {
                    const stringCell = String(cell || '');
                    if (stringCell.includes(',') || stringCell.includes('\n') || stringCell.includes('"')) {
                        return `"${stringCell.replace(/"/g, '""')}"`;
                    }
                    return stringCell;
                }).join(',')
            ).join('\n');
            downloadFile(title + '.csv', csvContent, 'text/csv;charset=utf-8;');
        });

        document.getElementById('share-btn').addEventListener('click', async function() {
            const title = titleField.value;
            const url = window.location.href;
            if (navigator.share) {
                try {
                    await navigator.share({ title: `Spreadsheet: ${title}`, text: `Check out my spreadsheet "${title}"`, url: url });
                } catch (err) { console.error('Share failed:', err.message); }
            } else {
                try {
                    await navigator.clipboard.writeText(url);
                    alert('Link to spreadsheet copied to clipboard!');
                } catch (err) { alert('Failed to copy link.'); }
            }
        });
    });
</script>
<?php 
endif;
?>

<script>
    function toggleDropdown(id) {
        document.querySelectorAll('.origin-top-right').forEach(function(menu) {
            if (menu.id !== id) {
                menu.classList.add('hidden');
            }
        });
        document.getElementById(id).classList.toggle('hidden');
    }
</script>

<?php 
if ($link) { mysqli_close($link); }
?>