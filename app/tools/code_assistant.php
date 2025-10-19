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
    <title>Code Assistant - Master AI Hub</title>
    <link rel="stylesheet" href="../../css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <style>
        /* Optional: Make text area taller for code */
        .chat-input-area textarea {
            min-height: 80px; /* Adjust as needed */
            resize: vertical;
             /* Allow vertical resizing */
             line-height: 1.4; /* Improve readability */
        }
    </style>
</head>
<body class="chat-app-body">
    
    <div id="overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99;" onclick="toggleSidebar()"></div>

    <aside class="chat-sidebar" id="chat-sidebar">
        <div>
            
            <a href="code_assistant.php" class="new-chat-btn">
                <span>⊕ New Code Session</span>
            </a>
        </div>
        
        <nav class="chat-history-list">
            <h3>Recent Sessions</h3>
            
            <a href="#">PHP cURL example...</a>
            <a href="#">JavaScript Array map...</a>
            <a href="#">Python Flask setup...</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../dashboard.php">← Back to Hub</a>
            <a href="../../backend/auth_handlers/handle_logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="chat-main">
        <header class="chat-main-header">
            <button class="menu-toggle" id="menu-toggle" onclick="toggleSidebar()">☰</button>
            <h2>Code Assistant</h2>
        </header>

                <div class="chat-messages" id="chat-messages">
            <div class="message bot" style="opacity: 1; transform: none;">
                Hello! How can I help you with your code today? Paste your code or ask a question.
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
        // Get textarea instead of input
        const userInput = document.getElementById('user-input'); 
        const chatBox = document.getElementById('chat-messages');
        const sidebar = document.getElementById('chat-sidebar');
        const overlay = document.getElementById('overlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        }

        let chatHistory = [
            // NEW: Add a system message to guide the AI
            { role: 'system', content: 'You are a helpful code assistant. Analyze the code provided, answer questions about programming concepts, write code snippets, explain errors, and offer suggestions for improvement. Format code blocks using Markdown.' },
            { role: 'assistant', content: 'Hello! How can I help you with your code today? Paste your code or ask a question.' }
        ];

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const messageText = userInput.value.trim();
            if (messageText === "") return;

            appendMessage('user', messageText);
            chatHistory.push({ role: 'user', content: messageText });
            userInput.value = ""; 
            // Optional: Reset textarea height after sending
            // userInput.style.height = 'auto'; 
            fetchMessage(chatHistory);
        });

        // Auto-resize textarea (Optional but nice)
        userInput.addEventListener('input', () => {
            userInput.style.height = 'auto';
            userInput.style.height = (userInput.scrollHeight) + 'px';
        });

function appendMessage(sender, text) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', sender);

    if (sender === 'bot') {
        const parts = text.split(/(```[\s\S]*?```)/g);

        parts.forEach(part => {
            if (part.startsWith('```') && part.endsWith('```')) {
                // --- HIGHLIGHT.JS INTEGRATION ---
                // 1. Extract code and potential language hint
                const languageMatch = part.match(/^```(\w+)?\s*\n?/); // Match ```java or ```python etc.
                const language = languageMatch && languageMatch[1] ? languageMatch[1].toLowerCase() : 'plaintext'; // Default to plaintext
                const codeContent = part.substring(
                    languageMatch ? languageMatch[0].length : 3, // Start after ``` or ```lang\n
                    part.length - 3 // End before ```
                ).trim();
                // --- END INTEGRATION ---

                const pre = document.createElement('pre');
                const code = document.createElement('code');

                // --- HIGHLIGHT.JS INTEGRATION ---
                // 2. Add the language class for highlight.js
                code.classList.add(`language-${language}`); 
                // --- END INTEGRATION ---

                code.textContent = codeContent; 
                pre.appendChild(code);

                const header = document.createElement('div');
                header.classList.add('code-block-header');
                const copyBtn = document.createElement('button');
                copyBtn.classList.add('copy-code-btn');
                copyBtn.textContent = 'Copy Code';
                copyBtn.onclick = () => { /* ... (copy logic remains the same) ... */ };
                copyBtn.onclick = () => {
                     navigator.clipboard.writeText(codeContent).then(() => {
                        copyBtn.textContent = 'Copied!';
                        setTimeout(() => { copyBtn.textContent = 'Copy Code'; }, 2000); 
                     }).catch(err => {
                        console.error('Failed to copy code: ', err);
                        copyBtn.textContent = 'Error';
                     });
                };
                header.appendChild(copyBtn);

                messageDiv.appendChild(header);
                messageDiv.appendChild(pre);

                // --- HIGHLIGHT.JS INTEGRATION ---
                // 3. Apply highlighting to the specific code block
                // We do this *after* appending to the DOM
                hljs.highlightElement(code); 
                // --- END INTEGRATION ---

            } else if (part.trim() !== '') {
                // Regular text handling remains the same
                // To prevent rendering HTML tags in regular text, create a span and set textContent
                const span = document.createElement('span');
                span.textContent = part;
                messageDiv.appendChild(span);
            }
        });

    } else {
         messageDiv.textContent = text;
    }

    chatBox.appendChild(messageDiv);
    void messageDiv.offsetWidth; 
    chatBox.scrollTop = chatBox.scrollHeight;
}

        async function fetchMessage(history) {
            const thinkingDiv = document.createElement('div');
            thinkingDiv.classList.add('message', 'bot');
            thinkingDiv.textContent = '...';
            chatBox.appendChild(thinkingDiv);
            void thinkingDiv.offsetWidth; 
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                 // Point to the NEW backend endpoint
                const response = await fetch('../../backend/api/handle_code_assistant.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: history }) 
                });
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
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