<?php
// File: /pages/flashcards.php
// Purpose: A full-featured, dynamic page to manage flashcard collections and study them.

// --- SETUP ---
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// --- INITIALIZATION ---
$error_message = '';
$success_message = '';
$collections = [];
$user_id   = (int)$_SESSION['id'];
$tenant_id = (int)($_SESSION['tenant_id'] ?? 0);

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
        // Verify the collection belongs to this user+tenant before fetching cards
        $sql = "SELECT f.id, f.question, f.answer FROM flashcards f
                JOIN flashcard_collections fc ON f.collection_id = fc.id
                WHERE f.collection_id = ? AND fc.user_id = ? AND fc.tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $collection_id, $user_id, $tenant_id);
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

// --- CRUD LOGIC FOR PAGE RELOADS (Collections) ---
if ($link && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify()) { $error_message = "Security token mismatch. Please try again."; goto render; }

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
        $sql = "UPDATE flashcard_collections SET title = ?, description = ? WHERE id = ? AND user_id = ? AND tenant_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssiii", $title, $description, $collection_id, $user_id, $tenant_id);
            if (mysqli_stmt_execute($stmt)) { header("Location: flashcards.php?success=2"); exit(); } 
            else { $error_message = "Error updating collection."; }
        }
    }
    
    // DELETE: Remove a collection (and its associated cards)
    if (isset($_POST['delete_collection'])) {
        $collection_id = trim($_POST['collection_id']);
        mysqli_begin_transaction($link);
        try {
            $sql_cards = "DELETE FROM flashcards WHERE collection_id = ? AND user_id = ?";
            $stmt_cards = mysqli_prepare($link, $sql_cards);
            mysqli_stmt_bind_param($stmt_cards, "ii", $collection_id, $user_id);
            mysqli_stmt_execute($stmt_cards);
            mysqli_stmt_close($stmt_cards);

            $sql_coll = "DELETE FROM flashcard_collections WHERE id = ? AND user_id = ? AND tenant_id = ?";
            $stmt_coll = mysqli_prepare($link, $sql_coll);
            mysqli_stmt_bind_param($stmt_coll, "iii", $collection_id, $user_id, $tenant_id);
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

    // AI BULK CREATE CARDS
    if (isset($_POST['ai_generate_cards'])) {
        $collection_id = intval($_POST['collection_id']);
        $cards_json = $_POST['cards_json'] ?? '';
        $cards = json_decode($cards_json, true);
        if ($cards && is_array($cards)) {
            $sql = "INSERT INTO flashcards (user_id, collection_id, question, answer) VALUES (?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                foreach ($cards as $card) {
                    $q = trim($card['question'] ?? '');
                    $a = trim($card['answer'] ?? '');
                    if (!empty($q) && !empty($a)) {
                        mysqli_stmt_bind_param($stmt, "iiss", $user_id, $collection_id, $q, $a);
                        mysqli_stmt_execute($stmt);
                    }
                }
                mysqli_stmt_close($stmt);
                header("Location: flashcards.php?success=5");
                exit();
            }
        }
        $error_message = "Failed to save AI-generated cards.";
    }

    // CREATE CARD
    if (isset($_POST['add_card'])) {
        $collection_id = trim($_POST['collection_id']);
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $sql = "INSERT INTO flashcards (user_id, collection_id, question, answer) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiss", $user_id, $collection_id, $question, $answer);
            if (mysqli_stmt_execute($stmt)) { header("Location: flashcards.php?success=4"); exit(); } 
            else { $error_message = "Error adding card."; }
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
        case 5: $success_message = "AI-generated flashcards added successfully."; break;
    }
}

// --- READ: Fetch all flashcard collections for the current user ---
if ($link) {
    $sql = "SELECT fc.id, fc.title, fc.description, COUNT(f.id) as card_count 
            FROM flashcard_collections fc
            LEFT JOIN flashcards f ON fc.id = f.collection_id
            WHERE fc.user_id = ? AND fc.tenant_id = ?
            GROUP BY fc.id
            ORDER BY fc.created_at DESC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $collections[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// --- RENDER PAGE ---
render:
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
        <button onclick="openModal('addCollectionModal')" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700">
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
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex flex-col justify-between h-48">
                <h3 class="font-bold text-lg mb-6 text-gray-800"><?php echo htmlspecialchars($collection['title']); ?></h3>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button <?php if($collection['card_count'] == 0) echo 'disabled'; ?> onclick="startLearnSession(<?php echo $collection['id']; ?>, '<?php echo htmlspecialchars(addslashes($collection['title'])); ?>')" class="shrink-0 bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 disabled:bg-gray-300 disabled:text-gray-500">
                            Learn
                        </button>
                        <button onclick="openAddCardModal(<?php echo $collection['id']; ?>)" class="shrink-0 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200">
                            Add Card
                        </button>
                        <button onclick="openAIGenerateModal(<?php echo $collection['id']; ?>)" class="shrink-0 bg-green-100 text-green-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-200">
                            <i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>AI Generate
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="openEditCollectionModal(<?php echo htmlspecialchars(json_encode($collection)); ?>)" class="w-10 h-10 flex items-center justify-center bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 shrink-0">
                            <i data-lucide="settings-2" class="w-5 h-5"></i>
                        </button>
                        <form method="POST" action="flashcards.php" onsubmit="return confirm('Are you sure? This will delete the collection and all cards inside it.');" class="m-0">
                            <input type="hidden" name="collection_id" value="<?php echo $collection['id']; ?>">
                            <button type="submit" name="delete_collection" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-100 shrink-0">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
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
        <form method="POST" action="flashcards.php"><?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border rounded-md p-2"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('addCollectionModal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="add_collection" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Create</button>
            </div>
        </form>
    </div>
</div>

<div id="editCollectionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Edit Collection</h2>
        <form method="POST" action="flashcards.php"><?php echo csrf_field(); ?>
            <input type="hidden" name="collection_id" id="edit_collection_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" id="edit_collection_title" class="mt-1 block w-full border rounded-md p-2" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" id="edit_collection_description" rows="3" class="mt-1 block w-full border rounded-md p-2"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('editCollectionModal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="update_collection" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="addCardModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
        <h2 class="text-2xl font-semibold mb-6">Add New Card</h2>
        <form method="POST" action="flashcards.php"><?php echo csrf_field(); ?>
            <input type="hidden" name="collection_id" id="add_card_collection_id">
            <div class="mb-4">
                <label class="block text-sm font-medium">Question</label>
                <textarea name="question" rows="4" class="mt-1 block w-full border rounded-md p-2" required></textarea>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium">Answer</label>
                <textarea name="answer" rows="4" class="mt-1 block w-full border rounded-md p-2" required></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeModal('addCardModal')" class="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" name="add_card" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Add Card</button>
            </div>
        </form>
    </div>
</div>

<div id="learnModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-100 rounded-lg shadow-xl p-8 w-full max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold" id="learn-modal-title">Learning...</h2>
            <button onclick="closeModal('learnModal')" class="text-gray-500 hover:text-gray-800">&times;</button>
        </div>
        
        <div id="learn-content" class="text-center">
            <div class="flashcard-container mb-4">
                <div id="flashcard" class="flashcard">
                    <div class="flashcard-face flashcard-front"><p id="flashcard-question" class="text-2xl"></p></div>
                    <div class="flashcard-face flashcard-back"><p id="flashcard-answer" class="text-xl"></p></div>
                </div>
            </div>
             <button onclick="document.getElementById('flashcard').classList.toggle('is-flipped')" class="w-full bg-white border border-gray-300 px-6 py-3 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 mb-6">
                Flip Card
            </button>
            <div class="flex items-center justify-between">
                <button id="prev-card-btn" class="bg-gray-200 px-4 py-2 rounded-lg">&lt; Prev</button>
                <p id="card-progress" class="text-gray-600 font-medium"></p>
                <button id="next-card-btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg">Next &gt;</button>
            </div>
        </div>
        <div id="learn-loading" class="text-center text-gray-500 py-16">Loading cards...</div>
        <div id="learn-no-cards" class="text-center text-gray-500 py-16 hidden">This collection has no cards to learn.</div>
    </div>
</div>

<script>
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

    // --- AI Generate Flashcards ---
    function openAIGenerateModal(collectionId) {
        document.getElementById('ai_gen_collection_id').value = collectionId;
        document.getElementById('ai-gen-preview').classList.add('hidden');
        document.getElementById('ai-gen-preview-list').innerHTML = '';
        document.getElementById('ai-gen-topic').value = '';
        document.getElementById('ai-gen-error').classList.add('hidden');
        openModal('aiGenerateModal');
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('ai-gen-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const topic   = document.getElementById('ai-gen-topic').value.trim();
            const count   = document.getElementById('ai-gen-count').value;
            const btn     = document.getElementById('ai-gen-btn');
            const errDiv  = document.getElementById('ai-gen-error');
            errDiv.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Generating...';

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/ai-assist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
                    body: JSON.stringify({ feature: 'flashcards_generate', context: topic, count: parseInt(count) })
                });
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                const cards = JSON.parse(data.result);
                if (!Array.isArray(cards) || cards.length === 0) throw new Error('No cards were generated.');

                document.getElementById('ai-gen-cards-json').value = JSON.stringify(cards);

                const list = document.getElementById('ai-gen-preview-list');
                list.innerHTML = '';
                cards.forEach((c, i) => {
                    list.innerHTML += `<div class="border rounded p-3 text-sm">
                        <p class="font-semibold text-gray-700">Q${i+1}: ${c.question}</p>
                        <p class="text-gray-500 mt-1">A: ${c.answer}</p>
                    </div>`;
                });
                document.getElementById('ai-gen-preview').classList.remove('hidden');
            } catch (err) {
                errDiv.textContent = 'Error: ' + err.message;
                errDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Generate';
            }
        });
    });

</script>

<!-- AI Generate Flashcards Modal -->
<div id="aiGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-green-600"></i> AI Generate Flashcards</h2>
        <form id="ai-gen-form">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Topic</label>
                <input type="text" id="ai-gen-topic" class="w-full border rounded-md p-2" placeholder="e.g. Photosynthesis, World War II, Python loops..." required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Cards</label>
                <select id="ai-gen-count" class="w-full border rounded-md p-2">
                    <option value="5">5 cards</option>
                    <option value="10" selected>10 cards</option>
                    <option value="15">15 cards</option>
                    <option value="20">20 cards</option>
                </select>
            </div>
            <div id="ai-gen-error" class="hidden bg-red-100 text-red-700 px-3 py-2 rounded mb-4 text-sm"></div>
            <div class="flex justify-end gap-2 mb-4">
                <button type="button" onclick="closeModal('aiGenerateModal')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                <button type="submit" id="ai-gen-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Generate</button>
            </div>
        </form>
        <div id="ai-gen-preview" class="hidden">
            <h3 class="font-semibold text-gray-700 mb-2">Preview</h3>
            <div id="ai-gen-preview-list" class="space-y-2 mb-4 max-h-60 overflow-y-auto"></div>
            <form method="POST">
                <input type="hidden" name="collection_id" id="ai_gen_collection_id">
                <input type="hidden" name="cards_json" id="ai-gen-cards-json">
                <div class="flex justify-end">
                    <button type="submit" name="ai_generate_cards" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">Save All Cards</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once(BASE_PATH . '/partials/footer.php');
if ($link) {
    mysqli_close($link);
}
?>