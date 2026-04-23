<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/flashcards.php
// Purpose: A full-featured, dynamic page to manage flashcard collections and study them.

// --- SETUP ---
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$error_message = '';
$success_message = '';
$collections = [];
$notes = []; // For the auto-generate feature
$user_id = $_SESSION["id"];
$tenant_id = $_SESSION["tenant_id"]; // SAAS Fix: Use tenant_id from session

// --- DATABASE CONNECTION CHECK ---
if (!$link) {
    $error_message = "Database connection failed: " . mysqli_connect_error();
}

// --- API-LIKE ACTIONS FOR JAVASCRIPT (FETCH) ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] == 'get_cards' && isset($_GET['collection_id'])) {
        $collection_id = intval($_GET['collection_id']);
        $cards = [];
        $sql = "SELECT f.id, f.question, f.answer FROM flashcards f JOIN flashcard_collections fc ON f.collection_id = fc.id WHERE f.collection_id = ? AND fc.tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $collection_id, $tenant_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while($row = mysqli_fetch_assoc($result)) {
                $cards[] = $row;
            }
            mysqli_stmt_close($stmt);
            echo json_encode(['success' => true, 'cards' => $cards]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch cards.']);
        }
    }
    exit(); 
}

// --- AJAX HANDLER FOR AI AUTO-GENERATED FLASHCARDS ---
if (isset($_POST['ajax_add_flashcards'])) {
    header('Content-Type: application/json');
    $cards_json = json_decode($_POST['cards'], true);
    $collection_id = intval($_POST['collection_id']);
    
    // Verify the collection belongs to this tenant
    $verify_sql = "SELECT id FROM flashcard_collections WHERE id = ? AND tenant_id = ?";
    if ($v_stmt = mysqli_prepare($link, $verify_sql)) {
        mysqli_stmt_bind_param($v_stmt, "ii", $collection_id, $tenant_id);
        mysqli_stmt_execute($v_stmt);
        mysqli_stmt_store_result($v_stmt);
        if (mysqli_stmt_num_rows($v_stmt) == 0) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized or collection not found.']);
            exit;
        }
        mysqli_stmt_close($v_stmt);
    }

    if (is_array($cards_json)) {
        $added = 0;
        $sql = "INSERT INTO flashcards (user_id, collection_id, question, answer) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            foreach ($cards_json as $c) {
                $q = trim($c['question'] ?? '');
                $a = trim($c['answer'] ?? '');
                if (!empty($q) && !empty($a)) {
                    mysqli_stmt_bind_param($stmt, "iiss", $user_id, $collection_id, $q, $a);
                    if (mysqli_stmt_execute($stmt)) {
                        $added++;
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }
        echo json_encode(['success' => true, 'added' => $added]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON returned by AI']);
    }
    exit;
}


// --- CRUD LOGIC FOR PAGE RELOADS (Collections) ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax_add_flashcards'])) {

    // CREATE: Add a new collection
    if (isset($_POST['add_collection'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        if (!empty($title)) {
            $sql = "INSERT INTO flashcard_collections (user_id, tenant_id, title, description) VALUES (?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iiss", $user_id, $tenant_id, $title, $description);
                if (mysqli_stmt_execute($stmt)) { header("Location: flashcards.php?success=1"); exit(); } 
                else { $error_message = "Error creating collection."; }
            }
        }
    }

    // UPDATE: Modify an existing collection
    if (isset($_POST['update_collection'])) {
        $collection_id = trim($_POST['collection_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sql = "UPDATE flashcard_collections SET title = ?, description = ? WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $title, $description, $collection_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) { header("Location: flashcards.php?success=2"); exit(); } 
            else { $error_message = "Error updating collection."; }
        }
    }
    
    // DELETE: Remove a collection (and its associated cards)
    if (isset($_POST['delete_collection'])) {
        $collection_id = trim($_POST['collection_id']);
        mysqli_begin_transaction($link);
        try {
            $sql_cards = "DELETE f FROM flashcards f JOIN flashcard_collections fc ON f.collection_id = fc.id WHERE f.collection_id = ? AND fc.tenant_id = ?";
            $stmt_cards = mysqli_prepare($link, $sql_cards);
            mysqli_stmt_bind_param($stmt_cards, "ii", $collection_id, $tenant_id);
            mysqli_stmt_execute($stmt_cards);
            mysqli_stmt_close($stmt_cards);

            $sql_coll = "DELETE FROM flashcard_collections WHERE id = ? AND tenant_id = ?";
            $stmt_coll = mysqli_prepare($link, $sql_coll);
            mysqli_stmt_bind_param($stmt_coll, "ii", $collection_id, $tenant_id);
            mysqli_stmt_execute($stmt_coll);
            mysqli_stmt_close($stmt_coll);

            mysqli_commit($link);
            header("Location: flashcards.php?success=3");
            exit();
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($link);
            $error_message = "Error deleting collection and its cards.";
        }
    }

    // CREATE CARD
    if (isset($_POST['add_card'])) {
        $collection_id = trim($_POST['collection_id']);
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $sql = "INSERT INTO flashcards (user_id, collection_id, question, answer) SELECT ?, ?, ?, ? FROM flashcard_collections WHERE id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iissii", $user_id, $collection_id, $question, $answer, $collection_id, $tenant_id);
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) { 
                header("Location: flashcards.php?success=4"); 
                exit(); 
            } else { 
                $error_message = "Error adding card. Make sure the collection exists."; 
            }
        }
    }
}

// Handle success messages from URL parameters
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1: $success_message = "Collection created successfully."; break;
        case 2: $success_message = "Collection updated successfully."; break;
        case 3: $success_message = "Collection deleted successfully."; break;
        case 4: $success_message = "Flashcard added successfully."; break;
    }
}

// --- READ: Fetch all flashcard collections for the current tenant ---
if ($link) {
    $sql = "SELECT fc.id, fc.title, fc.description, COUNT(f.id) as card_count 
            FROM flashcard_collections fc
            LEFT JOIN flashcards f ON fc.id = f.collection_id
            WHERE fc.tenant_id = ? 
            GROUP BY fc.id
            ORDER BY fc.created_at DESC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $collections[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Fetch user notes for the Auto-Generate feature
    $sql_notes = "SELECT id, title, content FROM notes WHERE tenant_id = ? ORDER BY updated_at DESC";
    if ($stmt = mysqli_prepare($link, $sql_notes)) {
        mysqli_stmt_bind_param($stmt, "i", $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            $notes[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// --- RENDER PAGE ---
require_once(BASE_PATH . '/partials/header.php');
?>
<style>
    .flashcard-container { perspective: 1000px; }
    .flashcard { width: 100%; height: 20rem; position: relative; transform-style: preserve-3d; transition: transform 0.6s; }
    .flashcard.is-flipped { transform: rotateY(180deg); }
    .flashcard-face { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; display: flex; align-items: center; justify-content: center; padding: 2rem; text-align: center; }
    .flashcard-front { background-color: #fff; border: 1px solid #ddd; border-radius: 0.5rem; }
    .flashcard-back { background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 0.5rem; transform: rotateY(180deg); }
</style>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold">Flashcards</h2>
        </div>
        <button onclick="openModal('addCollectionModal')" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
            Add New Collection
        </button>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($collections) && empty($error_message)): ?>
            <div class="col-span-full bg-white p-6 rounded-lg shadow text-center text-gray-500">
                <p>No flashcard collections found. Create one to get started!</p>
            </div>
        <?php else: ?>
            <?php foreach ($collections as $collection): ?>
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex flex-col justify-between h-[14rem]">
                <div>
                    <h3 class="font-bold text-lg mb-1 text-gray-800"><?php echo htmlspecialchars($collection['title']); ?></h3>
                    <p class="text-xs text-gray-500 mb-4"><?php echo $collection['card_count']; ?> Cards</p>
                </div>
                
                <div class="flex flex-col space-y-3">
                    <div class="flex items-center justify-between">
                        <button <?php if($collection['card_count'] == 0) echo 'disabled'; ?> onclick="startLearnSession(<?php echo $collection['id']; ?>, '<?php echo htmlspecialchars(addslashes($collection['title'])); ?>')" class="flex-1 mr-2 bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 disabled:bg-gray-300 disabled:text-gray-500 transition-colors text-center">
                            Learn
                        </button>
                        <div class="flex space-x-2">
                            <button onclick="openEditCollectionModal(<?php echo htmlspecialchars(json_encode($collection)); ?>)" class="w-9 h-9 flex items-center justify-center bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors" title="Edit Collection">
                                <i data-lucide="settings-2" class="w-4 h-4"></i>
                            </button>
                            <form method="POST" action="flashcards.php" onsubmit="return confirm('Are you sure? This will delete the collection and all cards inside it.');" class="m-0">
                                <input type="hidden" name="collection_id" value="<?php echo $collection['id']; ?>">
                                <button type="submit" name="delete_collection" class="w-9 h-9 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-colors" title="Delete Collection">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="openAutoGenerateModal(<?php echo $collection['id']; ?>)" class="flex-1 bg-purple-100 text-purple-700 px-2 py-2 rounded-lg text-xs font-semibold hover:bg-purple-200 transition-colors border border-purple-200 text-center">
                            ✨ Auto-Generate
                        </button>
                        <button onclick="openAddCardModal(<?php echo $collection['id']; ?>)" class="flex-1 bg-gray-100 text-gray-700 px-2 py-2 rounded-lg text-xs font-semibold hover:bg-gray-200 transition-colors border border-gray-200 text-center">
                            + Manual Card
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<div id="addCollectionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Create New Collection</h2>
        <form method="POST" action="flashcards.php">
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="mt-1 block w-full border rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('addCollectionModal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="add_collection" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Create</button>
            </div>
        </form>
    </div>
</div>

<div id="editCollectionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Edit Collection</h2>
        <form method="POST" action="flashcards.php">
            <input type="hidden" name="collection_id" id="edit_collection_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" id="edit_collection_title" class="mt-1 block w-full border rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" id="edit_collection_description" rows="3" class="mt-1 block w-full border rounded-md p-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('editCollectionModal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="update_collection" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="addCardModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Add Manual Card</h2>
        <form method="POST" action="flashcards.php">
            <input type="hidden" name="collection_id" id="add_card_collection_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Question</label>
                <textarea name="question" rows="4" class="mt-1 block w-full border rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required></textarea>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Answer</label>
                <textarea name="answer" rows="4" class="mt-1 block w-full border rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" required></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('addCardModal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" name="add_card" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Add Card</button>
            </div>
        </form>
    </div>
</div>

<div id="autoGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">✨ Auto-Generate Flashcards</h2>
        <form id="autoGenerateForm" onsubmit="event.preventDefault(); generateFlashcards(event);">
            <input type="hidden" id="auto_gen_collection_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Source Material</label>
                <select id="auto_gen_source" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" onchange="toggleCustomText(this.value)">
                    <option value="custom">Paste Custom Text</option>
                    <optgroup label="Your Saved Notes">
                        <?php foreach($notes as $note): ?>
                            <option value="<?php echo $note['id']; ?>"><?php echo htmlspecialchars($note['title']); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            
            <div id="custom_text_container" class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Paste Text</label>
                <textarea id="auto_gen_text" rows="6" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Paste your study material here..."></textarea>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('autoGenerateModal')" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">Cancel</button>
                <button type="submit" id="auto_gen_submit_btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Generate Cards</button>
            </div>
        </form>
    </div>
</div>

<div id="learnModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-100 rounded-lg shadow-xl p-8 w-full max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold" id="learn-modal-title">Learning...</h2>
            <button onclick="closeModal('learnModal')" class="text-gray-500 hover:text-gray-800 transition-colors text-2xl">&times;</button>
        </div>
        
        <div id="learn-content" class="text-center">
            <div class="flashcard-container mb-4">
                <div id="flashcard" class="flashcard cursor-pointer" onclick="this.classList.toggle('is-flipped')">
                    <div class="flashcard-face flashcard-front"><p id="flashcard-question" class="text-2xl whitespace-pre-wrap"></p></div>
                    <div class="flashcard-face flashcard-back"><p id="flashcard-answer" class="text-xl whitespace-pre-wrap"></p></div>
                </div>
            </div>
            
            <div class="mb-6">
                <button type="button" id="hint-btn" onclick="getHint()" class="text-sm bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg font-semibold hover:bg-yellow-200 transition-colors shadow-sm">
                    💡 Get a Hint
                </button>
                <div id="hint-area" class="hidden mt-3 p-3 bg-white border border-yellow-200 rounded-md text-sm text-gray-700 italic"></div>
            </div>

            <div class="flex items-center justify-between">
                <button id="prev-card-btn" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-800 hover:bg-gray-300 transition-colors">&lt; Prev</button>
                <p id="card-progress" class="text-gray-600 font-medium"></p>
                <button id="next-card-btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Next &gt;</button>
            </div>
        </div>
        <div id="learn-loading" class="text-center text-gray-500 py-16">Loading cards...</div>
        <div id="learn-no-cards" class="text-center text-gray-500 py-16 hidden">This collection has no cards to learn.</div>
    </div>
</div>

<script>
    // Embed PHP notes array into JavaScript for easy access
    const userNotes = <?php echo json_encode($notes); ?>;

    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

    function openEditCollectionModal(collection) {
        document.getElementById('edit_collection_id').value = collection.id;
        document.getElementById('edit_collection_title').value = collection.title;
        document.getElementById('edit_collection_description').value = collection.description;
        openModal('editCollectionModal');
    }
    
    function openAddCardModal(collectionId) {
        document.getElementById('add_card_collection_id').value = collectionId;
        openModal('addCardModal');
    }

    function openAutoGenerateModal(collectionId) {
        document.getElementById('auto_gen_collection_id').value = collectionId;
        document.getElementById('auto_gen_source').value = 'custom';
        toggleCustomText('custom');
        openModal('autoGenerateModal');
    }

    function toggleCustomText(val) {
        const container = document.getElementById('custom_text_container');
        if (val === 'custom') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    // --- AI AUTO-GENERATE SCRIPT ---
    async function generateFlashcards(event) {
        const btn = document.getElementById('auto_gen_submit_btn');
        const collectionId = document.getElementById('auto_gen_collection_id').value;
        const sourceSelect = document.getElementById('auto_gen_source').value;
        let textToProcess = "";

        if (sourceSelect === 'custom') {
            textToProcess = document.getElementById('auto_gen_text').value.trim();
        } else {
            // Find note content from JS variable
            const note = userNotes.find(n => n.id == sourceSelect);
            if (note) textToProcess = note.content;
        }

        // Strip HTML tags if it's a rich text note
        textToProcess = textToProcess.replace(/<[^>]*>?/gm, '').trim();

        if (!textToProcess) {
            alert('Please provide text or select a note.');
            return;
        }

        const originalBtnText = btn.innerHTML;
        btn.innerHTML = "⏳ Generating...";
        btn.disabled = true;

        try {
            const prompt = `You are a study assistant. Generate up to 10 key question-and-answer flashcards based on the following text. 
            CRITICAL: You MUST respond ONLY with a valid JSON array of objects. Do not wrap it in markdown blockquotes like \`\`\`json. Return raw JSON.
            Each object must have exactly two keys: "question" (string) and "answer" (string).
            
            Text: "${textToProcess}"`;

            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            let jsonStr = data.reply.replace(/```json/g, '').replace(/```/g, '').trim();
            let cards;
            try {
                cards = JSON.parse(jsonStr);
            } catch(e) {
                throw new Error("AI did not return valid JSON. Raw response: " + jsonStr);
            }

            if (!Array.isArray(cards)) throw new Error("AI did not return an array.");

            // Save via AJAX
            const formData = new FormData();
            formData.append('ajax_add_flashcards', '1');
            formData.append('collection_id', collectionId);
            formData.append('cards', JSON.stringify(cards));

            const saveResponse = await fetch('flashcards.php', {
                method: 'POST',
                body: formData
            });

            const saveData = await saveResponse.json();
            if (saveData.success) {
                alert(`Successfully generated and added ${saveData.added} flashcards!`);
                location.reload();
            } else {
                throw new Error(saveData.error || "Database save failed.");
            }

        } catch (error) {
            console.error(error);
            alert("Failed to generate flashcards: " + error.message);
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    }


    // --- LEARN SESSION LOGIC ---
    let currentCards = [];
    let currentIndex = 0;

    async function startLearnSession(collectionId, collectionTitle) {
        openModal('learnModal');
        document.getElementById('learn-modal-title').textContent = collectionTitle;
        document.getElementById('learn-content').classList.add('hidden');
        document.getElementById('learn-no-cards').classList.add('hidden');
        document.getElementById('learn-loading').classList.remove('hidden');

        try {
            const response = await fetch(`flashcards.php?action=get_cards&collection_id=${collectionId}`);
            const data = await response.json();

            if (data.success) {
                currentCards = data.cards;
                currentIndex = 0;
                document.getElementById('learn-loading').classList.add('hidden');

                if (currentCards.length > 0) {
                    displayCard();
                    document.getElementById('learn-content').classList.remove('hidden');
                } else {
                    document.getElementById('learn-no-cards').classList.remove('hidden');
                }
            } else {
                throw new Error(data.error || 'Failed to load cards.');
            }
        } catch (error) {
            console.error('Learn Session Error:', error);
            document.getElementById('learn-loading').textContent = 'Error loading cards.';
        }
    }
    
    function displayCard() {
        const card = currentCards[currentIndex];
        document.getElementById('flashcard-question').textContent = card.question;
        document.getElementById('flashcard-answer').textContent = card.answer;
        document.getElementById('card-progress').textContent = `${currentIndex + 1} / ${currentCards.length}`;
        document.getElementById('flashcard').classList.remove('is-flipped');
        
        // Hide hint when switching cards
        document.getElementById('hint-area').classList.add('hidden');
        document.getElementById('hint-area').textContent = '';
        
        document.getElementById('prev-card-btn').disabled = currentIndex === 0;
        document.getElementById('next-card-btn').disabled = currentIndex === currentCards.length - 1;
    }
    
    document.getElementById('next-card-btn').addEventListener('click', () => {
        if (currentIndex < currentCards.length - 1) {
            currentIndex++;
            displayCard();
        }
    });

    document.getElementById('prev-card-btn').addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            displayCard();
        }
    });

    // --- AI HINT GENERATOR SCRIPT ---
    async function getHint() {
        const card = currentCards[currentIndex];
        const hintBtn = document.getElementById('hint-btn');
        const hintArea = document.getElementById('hint-area');

        const originalText = hintBtn.innerHTML;
        hintBtn.disabled = true;
        hintBtn.innerHTML = "⏳ Thinking...";

        try {
            const prompt = `You are a study tutor. Provide a short, clever, and helpful hint for this flashcard question without revealing the exact answer. 
            Question: "${card.question}"
            Actual Answer (DO NOT REVEAL THIS): "${card.answer}"
            
            Keep the hint to one or two short sentences.`;

            const response = await fetch('<?php echo BASE_URL; ?>/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            hintArea.textContent = "💡 Hint: " + data.reply;
            hintArea.classList.remove('hidden');
            
        } catch (error) {
            console.error('Hint Error:', error);
            alert('Failed to get a hint: ' + error.message);
        } finally {
            hintBtn.innerHTML = originalText;
            hintBtn.disabled = false;
        }
    }

</script>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) {
    mysqli_close($link);
}
?>