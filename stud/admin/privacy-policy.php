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
$privacy_policy_content = 'PRIVACY NOTICE

Last updated February 11, 2023

This privacy notice for CloudOnex ("Company," "we," "us," or "our"), describes how and why we might collect, store, use, and/or share ("process") your information when you use our services ("Services"), such as when you:
Visit our website at https://www.cloudonex.com, or any website of ours that links to this privacy notice
Engage with us in other related ways, including any sales, marketing, or events
Questions or concerns? Reading this privacy notice will help you understand your privacy rights and choices. If you do not agree with our policies and practices, please do not use our Services. If you still have any questions or concerns, please contact us at ________.

SUMMARY OF KEY POINTS
This summary provides key points from our privacy notice, but you can find out more details about any of these topics by clicking the link following each key point or by using our table of contents below to find the section you are looking for. You can also click here to go directly to our table of contents.

What personal information do we process? When you visit, use, or navigate our Services, we may process personal information depending on how you interact with CloudOnex and the Services, the choices you make, and the products and features you use. Click here to learn more.

Do we process any sensitive personal information? We do not process sensitive personal information.

Do we receive any information from third parties? We do not receive any information from third parties.';

?>
<script src="https://cdn.tiny.cloud/1/0mgprpbs33v7zrkjsne3egwjvfuk9sd268xim5mrj8iz8npk/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea#policy_editor',
    plugins: 'lists link image table code help wordcount advlist autoresize',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image | table | code',
    height: 600,
    menubar: false,
    content_style: 'body { font-family:Inter,sans-serif; font-size:14px }'
  });
</script>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Post Editor / <span class="text-gray-500">Privacy Policy</span></h2>
        <button type="submit" form="policy-form" class="bg-purple-600 text-white px-8 py-2 rounded-lg font-semibold hover:bg-purple-700">Save</button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" id="policy-form">
            <div class="mb-6">
                <input type="text" value="Privacy Policy" class="text-2xl font-bold w-full border-b pb-2 focus:outline-none focus:border-purple-500">
            </div>
            <div>
                <textarea name="content" id="policy_editor"><?php echo htmlspecialchars($privacy_policy_content); ?></textarea>
            </div>
        </form>
    </div>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>