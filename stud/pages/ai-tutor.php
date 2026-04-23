<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// File: /pages/ai-tutor.php

// STEP 1: Include the master config file. This sets up all paths and the DB connection.
require_once(__DIR__ . '/../config.php');

// STEP 2: Include the header. This will automatically include the sidebar.
require_once(BASE_PATH . '/partials/header.php');

// STEP 3: Page-specific PHP logic to fetch chat history.
$chat_history = [];
if ($link) {
    // --- SAAS FIX: Use tenant_id and user_id from session ---
    $user_id = $_SESSION["id"];
    $tenant_id = $_SESSION["tenant_id"];
    
    // Fetch chats belonging to the current tenant
    $sql = "SELECT id, title, created_at FROM ai_chats WHERE tenant_id = ? ORDER BY created_at DESC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $tenant_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            $chat_history[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<main class="flex-1 bg-gray-100 flex h-[calc(100vh-64px)]">
    <div class="w-1/4 bg-white border-r border-gray-200 flex flex-col">
        <div class="p-4 border-b">
            <h2 class="text-xl font-semibold">Chat History</h2>
            <button id="newChatBtn" class="mt-4 w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                + New Chat
            </button>
        </div>
        <div id="chatHistoryList" class="flex-1 overflow-y-auto">
            <?php foreach ($chat_history as $chat): ?>
            <div class="p-4 border-b hover:bg-gray-50 cursor-pointer chat-history-item" data-chat-id="<?php echo $chat['id']; ?>">
                <h3 class="font-semibold text-sm truncate"><?php echo htmlspecialchars($chat['title']); ?></h3>
                <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($chat['created_at'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="w-3/4 flex flex-col">
        <div id="chatBox" class="flex-1 p-6 overflow-y-auto">
            <div id="initialMessage" class="text-center text-gray-500">
                <p class="text-2xl">StudyFlow AI Tutor</p>
                <p>Select a past conversation or start a new one.</p>
            </div>
        </div>
        <div class="p-4 bg-white border-t border-gray-200">
            <div id="chatError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline" id="chatErrorMessage"></span>
            </div>
            <form id="chatForm" class="flex items-center">
                <input type="text" id="userInput" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Type your message here..." autocomplete="off">
                <button type="submit" class="ml-4 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700" aria-label="Send">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 3 9-3 9 19-9Z"/><path d="M6 12h16"/></svg>
                </button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const userInput = document.getElementById('userInput');
    const chatBox = document.getElementById('chatBox');
    const newChatBtn = document.getElementById('newChatBtn');
    const chatHistoryList = document.getElementById('chatHistoryList');
    const initialMessage = document.getElementById('initialMessage');
    const chatError = document.getElementById('chatError');
    const chatErrorMessage = document.getElementById('chatErrorMessage');
    
    let currentChatId = null;

    function addChatHistoryEventListeners() {
        document.querySelectorAll('.chat-history-item').forEach(item => {
            item.addEventListener('click', () => {
                loadChatHistory(item.dataset.chatId);
            });
        });
    }
    addChatHistoryEventListeners();

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const message = userInput.value.trim();
        if (message) {
            if(initialMessage) initialMessage.style.display = 'none';
            appendMessage(message, 'user');
            userInput.value = '';
            sendMessageToServer(message);
        }
    });

    newChatBtn.addEventListener('click', () => {
        currentChatId = null;
        chatBox.innerHTML = '';
        if(initialMessage) initialMessage.style.display = 'block';
    });

    function appendMessage(message, sender, isError = false) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('flex', 'mb-4', sender === 'user' ? 'justify-end' : 'justify-start');
        
        const bubble = document.createElement('div');
        bubble.classList.add('px-4', 'py-2', 'rounded-lg', 'max-w-xl', 'whitespace-pre-wrap');
        
        if (isError) {
            bubble.classList.add('bg-red-100', 'text-red-800');
            bubble.innerHTML = `<strong>Error:</strong><br>${message}`;
        } else {
            bubble.classList.add(sender === 'user' ? 'bg-purple-600' : 'bg-gray-200', sender === 'user' ? 'text-white' : 'text-gray-800');
            bubble.textContent = message;
        }
        
        wrapper.appendChild(bubble);
        chatBox.appendChild(wrapper);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    async function sendMessageToServer(message) {
        const apiUrl = `<?php echo BASE_URL; ?>/api/ai-chat.php`;
        const typingIndicator = appendTypingIndicator();
        chatError.classList.add('hidden');

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message, chat_id: currentChatId })
            });
            
            const responseText = await response.text();
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error("The server returned an invalid JSON response. This is likely a fatal PHP error.");
                console.error("Raw Server Response:", responseText);
                throw new Error("The server encountered an error. Check the developer console for the raw PHP error message.");
            }

            typingIndicator.remove();

            if (data.error) {
                throw new Error(data.error);
            }
            
            if (!currentChatId && data.chat_id) {
                // It's a new chat, add it to the history list
                const newChatItem = document.createElement('div');
                newChatItem.classList.add('p-4', 'border-b', 'hover:bg-gray-50', 'cursor-pointer', 'chat-history-item');
                newChatItem.dataset.chatId = data.chat_id;
                newChatItem.innerHTML = `<h3 class="font-semibold text-sm truncate">Chat on ${new Date().toLocaleDateString()}</h3><p class="text-xs text-gray-500">${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</p>`;
                chatHistoryList.prepend(newChatItem);
                addChatHistoryEventListeners(); // Re-attach listeners
            }

            currentChatId = data.chat_id;
            appendMessage(data.reply, 'model');

        } catch (error) {
            typingIndicator.remove();
            console.error('Fetch Error:', error);
            chatErrorMessage.textContent = error.message;
            chatError.classList.remove('hidden');
        }
    }

    async function loadChatHistory(chatId) {
        const apiUrl = `<?php echo BASE_URL; ?>/api/load-chat.php`;
        currentChatId = chatId;
        chatBox.innerHTML = '<p class="text-center text-gray-500">Loading chat...</p>';
        chatError.classList.add('hidden');

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_id: chatId })
            });
            const data = await response.json();

            chatBox.innerHTML = '';
            if (data.error) throw new Error(data.error);
            
            data.forEach(item => appendMessage(item.message, item.sender));

        } catch (error) {
            console.error('Load History Error:', error);
            chatBox.innerHTML = '';
            chatErrorMessage.textContent = `Error loading chat: ${error.message}`;
            chatError.classList.remove('hidden');
        }
    }
    
    function appendTypingIndicator() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('flex', 'mb-4', 'justify-start', 'typing-indicator');
        wrapper.innerHTML = `<div class="px-4 py-2 rounded-lg max-w-xl bg-gray-200 text-gray-800">...</div>`;
        chatBox.appendChild(wrapper);
        chatBox.scrollTop = chatBox.scrollHeight;
        return wrapper;
    }
});
</script>

<?php 
// STEP 5: Include the footer to load JavaScript and close the HTML.
require_once(BASE_PATH . '/partials/footer.php'); 
?>