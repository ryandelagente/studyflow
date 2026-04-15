<?php
// File: /pages/contacts.php
// Purpose: A full-featured page to manage user contacts with list and form views.

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
$error_message = '';
$success_message = '';
$action = $_GET['action'] ?? 'list'; // 'list', 'create', 'edit'
$contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$contact_to_edit = null;

// --- DATABASE CONNECTION CHECK ---
if (!$link) {
    $error_message = "Database connection failed.";
}

// --- CRUD LOGIC ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify()) { $error_message = "Security token mismatch. Please try again."; goto render; }
    // CREATE
    if (isset($_POST['add_contact'])) {
        $first_name = trim($_POST['first_name']); $last_name = trim($_POST['last_name']); $title = trim($_POST['title']);
        $email = trim($_POST['email']); $phone = trim($_POST['phone']); $address = trim($_POST['address']);
        $city = trim($_POST['city']); $state = trim($_POST['state']); $zip = trim($_POST['zip']); $notes = trim($_POST['notes']);
        
        $sql = "INSERT INTO contacts (user_id, tenant_id, first_name, last_name, title, email, phone, address, city, state, zip, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iissssssssss", $user_id, $tenant_id, $first_name, $last_name, $title, $email, $phone, $address, $city, $state, $zip, $notes);
            if (mysqli_stmt_execute($stmt)) { header("Location: contacts.php?success=1"); exit(); }
        }
    }
    // UPDATE
    if (isset($_POST['update_contact'])) {
        $contact_id = intval($_POST['contact_id']);
        $first_name = trim($_POST['first_name']); $last_name = trim($_POST['last_name']); $title = trim($_POST['title']);
        $email = trim($_POST['email']); $phone = trim($_POST['phone']); $address = trim($_POST['address']);
        $city = trim($_POST['city']); $state = trim($_POST['state']); $zip = trim($_POST['zip']); $notes = trim($_POST['notes']);
        
        $sql = "UPDATE contacts SET first_name=?, last_name=?, title=?, email=?, phone=?, address=?, city=?, state=?, zip=?, notes=? WHERE id=? AND user_id=? AND tenant_id=?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssssiii", $first_name, $last_name, $title, $email, $phone, $address, $city, $state, $zip, $notes, $contact_id, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) { header("Location: contacts.php?success=2"); exit(); }
        }
    }
    // DELETE
    if (isset($_POST['delete_contact'])) {
        $contact_id_to_delete = intval($_POST['contact_id']);
        $sql = "DELETE FROM contacts WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $contact_id_to_delete, $user_id, $tenant_id);
            if(mysqli_stmt_execute($stmt)) {
                header("Location: contacts.php?success=3");
                exit();
            } else {
                $error_message = "Error deleting contact.";
            }
        }
    }
}

if (isset($_GET['success'])) {
    $msgs = [1 => "Contact added.", 2 => "Contact updated.", 3 => "Contact deleted."];
    $success_message = $msgs[(int)$_GET['success']] ?? '';
}
$success_message = $success_message ?: flash_get('success');
$error_message   = $error_message   ?: flash_get('error');

// --- READ DATA ---
$contacts = [];
if ($link) {
    if ($action === 'edit' && $contact_id > 0) {
        $sql = "SELECT * FROM contacts WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $contact_id, $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $contact_to_edit = mysqli_fetch_assoc($result);
            if (!$contact_to_edit) { header("Location: contacts.php"); exit(); }
        }
    } else {
        $sql = "SELECT * FROM contacts WHERE user_id = ? AND tenant_id = ? ORDER BY first_name ASC";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) { $contacts[] = $row; }
        }
    }
}

render:
require_once(BASE_PATH . '/partials/header.php');
?>

<main class="flex-1 p-8 overflow-y-auto">
    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($action === 'create' || ($action === 'edit' && isset($contact_to_edit))): ?>
    <div class="mb-8">
        <h2 class="text-3xl font-bold">Contact Details</h2>
    </div>
    <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
        <form method="POST"><?php echo csrf_field(); ?>
             <?php if ($action === 'edit'): ?>
                <input type="hidden" name="contact_id" value="<?php echo $contact_to_edit['id']; ?>">
            <?php endif; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium">First Name</label><input type="text" name="first_name" value="<?php echo htmlspecialchars($contact_to_edit['first_name'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-medium">Last Name</label><input type="text" name="last_name" value="<?php echo htmlspecialchars($contact_to_edit['last_name'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium">Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($contact_to_edit['title'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-medium">Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($contact_to_edit['email'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-medium">Phone</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($contact_to_edit['phone'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium">Address</label><input type="text" name="address" value="<?php echo htmlspecialchars($contact_to_edit['address'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-medium">City</label><input type="text" name="city" value="<?php echo htmlspecialchars($contact_to_edit['city'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-medium">State</label><input type="text" name="state" value="<?php echo htmlspecialchars($contact_to_edit['state'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium">Zip</label><input type="text" name="zip" value="<?php echo htmlspecialchars($contact_to_edit['zip'] ?? ''); ?>" class="mt-1 block w-full border rounded-md p-2"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium">Notes</label><textarea name="notes" rows="4" class="mt-1 block w-full border rounded-md p-2"><?php echo htmlspecialchars($contact_to_edit['notes'] ?? ''); ?></textarea></div>
            </div>
            <div class="flex justify-end space-x-4 mt-8">
                 <a href="contacts.php" class="bg-gray-200 px-6 py-2 rounded-lg font-semibold">Back</a>
                 <button type="submit" name="<?php echo $action === 'create' ? 'add_contact' : 'update_contact'; ?>" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold">Save</button>
            </div>
        </form>
    </div>

    <?php else: ?>
    <div class="flex justify-between items-center mb-8">
        <div><h2 class="text-3xl font-bold">Contacts</h2></div>
        <a href="contacts.php?action=create" class="bg-gray-800 text-white px-5 py-2 rounded-lg font-semibold hover:bg-gray-900">Add Contact</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
         <div class="flex justify-between items-center mb-4">
            <div class="flex items-center text-sm text-gray-600">
                <span>Show</span><select class="mx-2 border rounded-md p-1.5"><option>10</option></select><span>entries</span>
            </div>
            <div class="w-1/3"><input type="text" placeholder="Search..." class="border rounded-md p-2 w-full"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm border-b">
                        <th class="p-3 font-semibold">NAME</th>
                        <th class="p-3 font-semibold">TITLE</th>
                        <th class="p-3 font-semibold">OWNER</th>
                        <th class="p-3 font-semibold">EMAIL</th>
                        <th class="p-3 font-semibold">PHONE</th>
                        <th class="p-3 font-semibold text-right">MANAGE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr><td colspan="6" class="text-center p-8 text-gray-500">No contacts found.</td></tr>
                    <?php else: ?>
                        <?php 
                            $colors = ['bg-green-500', 'bg-blue-500', 'bg-red-500', 'bg-yellow-500', 'bg-indigo-500', 'bg-pink-500'];
                            $i = 0;
                        ?>
                        <?php foreach ($contacts as $contact): ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-800">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 <?php echo $colors[$i % count($colors)]; $i++; ?> rounded-full flex items-center justify-center text-white font-bold text-xs mr-3 shrink-0">
                                            <?php echo strtoupper(substr($contact['first_name'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></span>
                                    </div>
                                </td>
                                <td class="p-3 text-gray-600"><?php echo htmlspecialchars($contact['title']); ?></td>
                                <td class="p-3 text-gray-600">Liam P</td> <td class="p-3 text-gray-600"><?php echo htmlspecialchars($contact['email']); ?></td>
                                <td class="p-3 text-gray-600"><?php echo htmlspecialchars($contact['phone']); ?></td>
                                <td class="p-3 text-right">
                                    <div class="relative inline-block text-left">
                                        <button onclick="toggleDropdown('menu-<?php echo $contact['id']; ?>')" class="text-gray-400 hover:text-blue-500 p-1">
                                            <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                                        </button>
                                        <div id="menu-<?php echo $contact['id']; ?>" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-10">
                                            <div class="py-1">
                                                <a href="contacts.php?action=edit&id=<?php echo $contact['id']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                                                <form method="POST" action="contacts.php" onsubmit="return confirm('Are you sure you want to delete this contact?');" class="w-full"><?php echo csrf_field(); ?>
                                                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                    <button type="submit" name="delete_contact" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
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
        <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
            <div>Showing 1 to <?php echo count($contacts); ?> of <?php echo count($contacts); ?> entries</div>
            <div class="flex items-center"><a href="#" class="px-3 py-1 border rounded-l-md hover:bg-gray-100">Previous</a><a href="#" class="px-3 py-1 border bg-purple-600 text-white">1</a><a href="#" class="px-3 py-1 border rounded-r-md hover:bg-gray-100">Next</a></div>
        </div>
    </div>
    <?php endif; ?>
</main>
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
require_once(BASE_PATH . '/partials/footer.php');
if ($link) { mysqli_close($link); }
?>