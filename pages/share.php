<?php
// File: /pages/share.php

// 1. Include the configuration and header files
require_once(__DIR__ . '/../config.php');
require_once(BASE_PATH . '/partials/header.php');
?>
<style>
    /* Add a style for the drag-over effect */
    .drag-over {
        border-color: #6d28d9; /* purple-600 */
        background-color: #f5f3ff; /* purple-50 */
    }
</style>

<main class="flex-1 p-8 overflow-y-auto flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl">
        <h2 class="text-2xl font-bold mb-2 text-center">New Share Wizard</h2>
        <p class="text-gray-500 mb-6 text-center">What would you like to share?</p>
        
        <div id="share-type-selector" class="flex justify-center flex-wrap gap-x-6 gap-y-2 mb-8 text-sm text-gray-700">
            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="share_type" value="url" class="text-purple-600 focus:ring-purple-500"><span>A URL</span></label>
            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="share_type" value="file" class="text-purple-600 focus:ring-purple-500" checked><span>An image</span></label>
            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="share_type" value="file" class="text-purple-600 focus:ring-purple-500"><span>A video</span></label>
            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="share_type" value="file" class="text-purple-600 focus:ring-purple-500"><span>A zip file</span></label>
            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="share_type" value="text" class="text-purple-600 focus:ring-purple-500"><span>A text snippet</span></label>
        </div>

        <div id="file-form-section">
            <form action="<?php echo BASE_URL; ?>/pages/resources.php" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center transition-colors duration-200">
                    <div id="upload-prompt">
                        <p class="text-gray-500 mb-4">Drag and drop your file here</p>
                        <p class="text-gray-500 mb-4">or</p>
                        <label for="resource_file" class="cursor-pointer bg-gray-800 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-900 inline-block">Browse Files</label>
                        <input type="file" id="resource_file" name="resource_file" class="hidden">
                    </div>
                    <div id="file-display" class="hidden text-left">
                         <div class="mt-2 p-2 bg-gray-100 rounded flex items-center justify-between">
                            <span id="file-name" class="text-sm text-gray-800 break-all"></span>
                            <button type="button" id="remove-file-btn" class="text-red-500 hover:text-red-700 text-sm font-semibold ml-4">Remove</button>
                        </div>
                    </div>
                </div>
                <div id="upload-button-container" class="mt-6 hidden">
                    <button type="submit" name="upload_file" class="bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 w-full">Upload and Share</button>
                </div>
            </form>
        </div>
        
        <div id="url-form-section" class="hidden">
            <form action="<?php echo BASE_URL; ?>/pages/notes.php" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_note_from_share" value="1">
                <input type="hidden" name="note_type" value="url">
                <div>
                    <label for="url-title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="url-title" name="title" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div>
                    <label for="url-input" class="block text-sm font-medium text-gray-700">URL</label>
                    <input type="url" id="url-input" name="content" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="https://example.com" required>
                </div>
                <div class="pt-2">
                    <button type="submit" class="bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 w-full">Save</button>
                </div>
            </form>
        </div>

        <div id="text-form-section" class="hidden">
             <form action="<?php echo BASE_URL; ?>/pages/notes.php" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_note_from_share" value="1">
                <input type="hidden" name="note_type" value="text">
                <div>
                    <label for="text-title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="text-title" name="title" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div>
                    <label for="text-content" class="block text-sm font-medium text-gray-700">Text</label>
                    <textarea id="text-content" name="content" rows="6" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 w-full">Save</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shareTypeRadios = document.querySelectorAll('input[name="share_type"]');
    const fileSection = document.getElementById('file-form-section');
    const urlSection = document.getElementById('url-form-section');
    const textSection = document.getElementById('text-form-section');

    const updateFormView = () => {
        const selectedValue = document.querySelector('input[name="share_type"]:checked').value;
        
        // Hide all sections first
        fileSection.classList.add('hidden');
        urlSection.classList.add('hidden');
        textSection.classList.add('hidden');

        // Show the correct section
        if (selectedValue === 'file') {
            fileSection.classList.remove('hidden');
        } else if (selectedValue === 'url') {
            urlSection.classList.remove('hidden');
        } else if (selectedValue === 'text') {
            textSection.classList.remove('hidden');
        }
    };

    shareTypeRadios.forEach(radio => radio.addEventListener('change', updateFormView));

    // --- File Upload Logic (from previous step) ---
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('resource_file');
    const uploadPrompt = document.getElementById('upload-prompt');
    const fileDisplay = document.getElementById('file-display');
    const fileNameDisplay = document.getElementById('file-name');
    const removeFileBtn = document.getElementById('remove-file-btn');
    const uploadBtnContainer = document.getElementById('upload-button-container');

    const handleFileSelect = (file) => {
        if (file) {
            fileNameDisplay.textContent = `${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
            uploadPrompt.classList.add('hidden');
            fileDisplay.classList.remove('hidden');
            uploadBtnContainer.classList.remove('hidden');
        }
    };
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eName => dropZone.addEventListener(eName, e => { e.preventDefault(); e.stopPropagation(); }));
    ['dragenter', 'dragover'].forEach(eName => dropZone.addEventListener(eName, () => dropZone.classList.add('drag-over')));
    ['dragleave', 'drop'].forEach(eName => dropZone.addEventListener(eName, () => dropZone.classList.remove('drag-over')));
    
    dropZone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) handleFileSelect(e.target.files[0]);
    });

    removeFileBtn.addEventListener('click', () => {
        fileInput.value = '';
        fileDisplay.classList.add('hidden');
        uploadPrompt.classList.remove('hidden');
        uploadBtnContainer.classList.add('hidden');
    });

    // Initial view setup
    updateFormView();
});
</script>

<?php
// 3. Include the footer file
require_once(BASE_PATH . '/partials/footer.php');
?>