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

// In a real application, you would fetch these values from a database settings table.
$landing_data = [
    'hero_title' => 'The Ultimate Productivity App for Students',
    'hero_subtitle' => 'Stop juggling apps. To-dos, notes, flashcards, and an AI tutor—all in one place.',
    'features_title' => 'Boost productivity with an all-in-one toolkit',
    'faq_title' => 'Frequently Asked Questions',
];

?>

<main class="flex-1 p-8 overflow-y-auto bg-gray-50">
    <h2 class="text-2xl font-bold text-gray-800 mb-8">Landing Page</h2>

    <form method="POST">
        <div class="space-y-8">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-4 mb-4">Hero Section</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" value="<?php echo htmlspecialchars($landing_data['hero_title']); ?>" class="mt-1 block w-full border rounded-md p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                            <textarea rows="3" class="mt-1 block w-full border rounded-md p-2"><?php echo htmlspecialchars($landing_data['hero_subtitle']); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Button Text</label>
                            <input type="text" value="Get Started for Free" class="mt-1 block w-full border rounded-md p-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Background Image</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-4 mb-4">Features Section</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Section Title</label>
                    <input type="text" value="<?php echo htmlspecialchars($landing_data['features_title']); ?>" class="mt-1 block w-full border rounded-md p-2">
                </div>
                </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                 <div class="flex justify-between items-center border-b pb-4 mb-4">
                    <h3 class="text-xl font-semibold">FAQ Section</h3>
                    <button type="button" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Add FAQ</button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Question 1</label>
                            <input type="text" value="What is the difference between the monthly and yearly plans?" class="mt-1 block w-full border rounded-md p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Answer 1</label>
                            <input type="text" value="The yearly plan is 10% off the monthly price." class="mt-1 block w-full border rounded-md p-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Question 2</label>
                            <input type="text" value="How do I cancel my subscription?" class="mt-1 block w-full border rounded-md p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Answer 2</label>
                            <input type="text" value="You can cancel anytime from your account settings." class="mt-1 block w-full border rounded-md p-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-right">
            <button type="submit" class="bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700">Save Changes</button>
        </div>
    </form>
</main>

<?php require_once(BASE_PATH . '/partials/footer.php'); ?>