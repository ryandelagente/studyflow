<?php
// Initialize the session
session_start();
 
// Check if the user is logged in and is an admin/super_admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'admin' && $_SESSION["role"] !== 'super_admin')){
    header("location: ../login.php");
    exit;
}
 
require_once(__DIR__ . '/../config.php');
require_once(BASE_PATH . '/partials/header.php');

// In a real application, you would fetch this content from the database.
$terms_content = 'TERMS OF SERVICE

Last updated February 11, 2023

AGREEMENT TO OUR LEGAL TERMS

We are CloudOnex ("Company," "we," "us," "our"). We operate the website at https://www.cloudonex.com (the "Site"), as well as any other related products and services that refer or link to these legal terms (the "Legal Terms") (collectively, the "Services").

These Legal Terms constitute a legally binding agreement made between you, whether personally or on behalf of an entity ("you"), and CloudOnex, concerning your access to and use of the Services. You agree that by accessing the Services, you have read, understood, and agreed to be bound by all of these Legal Terms.';

?>
<script src="https://cdn.tiny.cloud/1/0mgprpbs33v7zrkjsne3egwjvfuk9sd268xim5mrj8iz8npk/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea#terms_editor',
    plugins: 'lists link image table code help wordcount advlist autoresize',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image | table | code',
    height: 600,
    menubar: false,
    content_style: 'body { font-family:Inter,sans-serif; font-size:14px }'
  });
</script>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Post Editor / <span class="text-gray-500">Terms of Service</span></h2>
        <button type="submit" form="terms-form" class="bg-purple-600 text-white px-8 py-2 rounded-lg font-semibold hover:bg-purple-700">Save</button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" id="terms-form">
            <div class="mb-6">
                <input type="text" value="Terms of Service" class="text-2xl font-bold w-full border-b pb-2 focus:outline-none focus:border-purple-500">
            </div>
            <div>
                <textarea name="content" id="terms_editor"><?php echo htmlspecialchars($terms_content); ?></textarea>
            </div>
        </form>
    </div>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>