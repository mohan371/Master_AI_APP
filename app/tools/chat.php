<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'student'){
    header("location: ../../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot - Master AI Hub</title>
    <link rel="stylesheet" href="../../css/style.css"> 
</head>
<body class="chat-app-body">
    
    <div id="overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99;" onclick="toggleSidebar()"></div>

    <aside class="chat-sidebar" id="chat-sidebar">
        <div>
            <a href="chat.php" class="new-chat-btn">
                <span>⊕ New Chat</span>
            </a>
        </div>
        
        <nav class="chat-history-list">
            <h3>Recent Chats</h3>
            <a href="#">How to use PHP cURL...</a>
            <a href="#">Image Generation Ideas...</a>
            <a href="#">What is Stable Diffusion...</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../dashboard.php">← Back to Hub</a>
            <a href="../../backend/auth_handlers/handle_logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="chat-main">
        <header class="chat-main-header">
            <button class="menu-toggle" id="menu-toggle" onclick="toggleSidebar()">☰</button>
            <h2>Master AI Chat</h2>
        </header>

        <div class="chat-messages" id="chat-messages">
            <div class="message bot" style="opacity: 1; transform: none;">
                Hello! How can I help you today?
            </div>
        </div>

        <div class="chat-input-area">
            <form class="input-form" id="chat-input-form">
                <input type="text" id="user-input" placeholder="Type your message..." autocomplete="off">
                <button type="submit">➤</button>
            </form>
        </div>
    </main>

    <script>
        const chatForm = document.getElementById('chat-input-form');
        const userInput = document.getElementById('user-input');
        const chatBox = document.getElementById('chat-messages');
        const sidebar = document.getElementById('chat-sidebar');
        const overlay = document.getElementById('overlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        }

        let chatHistory = [
            { role: 'assistant', content: 'Hello! How can I help you today?' }
        ];

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const messageText = userInput.value.trim();
            if (messageText === "") return;

            appendMessage('user', messageText);
            chatHistory.push({ role: 'user', content: messageText });
            userInput.value = ""; 
            fetchMessage(chatHistory);
        });

        // --- UPDATED appendMessage Function ---
        function appendMessage(sender, text) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', sender);
            messageDiv.textContent = text;
            chatBox.appendChild(messageDiv);
            
            // This is new: We force a 'reflow' so the animation
            // restarts every single time a new message is added.
            void messageDiv.offsetWidth; 

            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function fetchMessage(history) {
            const thinkingDiv = document.createElement('div');
            thinkingDiv.classList.add('message', 'bot');
            thinkingDiv.textContent = '...';
            chatBox.appendChild(thinkingDiv);
            void thinkingDiv.offsetWidth; // Trigger animation
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                const response = await fetch('../../backend/api/chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: history }) 
                });
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                // --- UPDATED: Remove '...' and add real reply ---
                chatBox.removeChild(thinkingDiv);
                appendMessage('bot', data.reply);
                chatHistory.push({ role: 'assistant', content: data.reply });

            } catch (error) {
                console.error('Fetch error:', error);
                chatBox.removeChild(thinkingDiv);
                appendMessage('bot', 'Sorry, something went wrong. Please try again.');
            }
        }
    </script>
</body>
</html>