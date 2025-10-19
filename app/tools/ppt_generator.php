<?php
session_start();
// Security check: ensure user is logged in and is a student
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'student'){
    header("location: ../../auth/login.php"); // Redirect to login if not authorized
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPT Content Generator - Master AI Hub</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <style>
        /* Add style for the Generate PPTX button container */
        .generate-pptx-container {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background-color: var(--bg-color-dark);
        }
        .generate-pptx-container .btn-tool { /* Reuse hub button style */
            padding: 0.8rem 1.5rem;
        }
        .generate-pptx-container .status {
             font-size: 0.9rem;
             color: var(--text-secondary);
             margin-top: 0.5rem;
             /* Allow space for the download button/link */
             min-height: 50px;
        }
        /* Simple loader for PPTX button */
        .loader-ppt {
             font-style: italic;
             color: var(--text-secondary);
        }

    </style>
</head>
<body class="chat-app-body">

    <div id="overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99;" onclick="toggleSidebar()"></div>

    <aside class="chat-sidebar" id="chat-sidebar">
        <div>
            <a href="ppt_generator.php" class="new-chat-btn">
                <span>⊕ New Presentation</span>
            </a>
        </div>
        <nav class="chat-history-list">
            <h3>Recent Topics</h3>
            <a href="#">Future of AI...</a>
            <a href="#">Benefits of Exercise...</a>
            <a href="#">Intro to Python...</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../dashboard.php">← Back to Hub</a>
            <a href="../../backend/auth_handlers/handle_logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="chat-main">
        <header class="chat-main-header">
            <button class="menu-toggle" id="menu-toggle" onclick="toggleSidebar()">☰</button>
            <h2>PPT Content Generator</h2>
        </header>

        <div class="chat-messages" id="chat-messages">
            <div class="message bot" style="opacity: 1; transform: none;">
                Hello! What topic would you like me to generate presentation content for?
            </div>
        </div>

        <div class="generate-pptx-container" id="pptx-button-container" style="display: none;">
             <button class="btn-tool" id="generate-pptx-btn">Generate PPTX File</button>
             <div class="status" id="pptx-status"></div>
        </div>

        <div class="chat-input-area">
            <form class="input-form" id="chat-input-form">
                <input type="text" id="user-input" placeholder="Enter presentation topic..." autocomplete="off">
                <button type="submit" id="send-btn">➤</button> </form>
        </div>
    </main>

    <script>
        // --- Function Definitions (Keep outside DOMContentLoaded) ---
        function toggleSidebar() {
            const sidebar = document.getElementById('chat-sidebar'); // Get elements inside function
            const overlay = document.getElementById('overlay');
            if (!sidebar || !overlay) return; // Add checks
            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        }

        // Function to add a message to the chat interface (with PPT parsing)
        function appendMessage(sender, text) {
            const chatBox = document.getElementById('chat-messages'); // Get chatBox inside function
             if (!chatBox) {
                 console.error("Chatbox element not found in appendMessage");
                 return;
             }
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', sender);

            if (sender === 'bot') {
                // --- FINAL PPT Parsing Logic (v4 - Matches latest response) ---
                window.lastBotReplyText = text; // Store raw text globally for PPTX button
                const lines = text.trim().split('\n');
                let currentSlideDiv = null;
                let currentPointsList = null;
                let currentNotesDiv = null;
                let expectingTitleText = false; // State flag
                let slideFound = false; // Flag to check if any slides were parsed

                lines.forEach(line => {
                    line = line.trim();
                    if (!line) return; // Skip empty lines

                    // Match patterns
                    const slideMatch = line.match(/^### Slide \d+:(.*)/i);
                    const titleMarkerMatch = line.match(/^#### Title:/i);
                    const notesMatch = line.match(/^\*\*Speaker Note:\*\*(.*)/i);
                    const pointMatch = line.match(/^- (\*\*.*?\*\*):(.*)/); // Match "- **Point:** Rest"
                    const codeBlockStartMatch = line.match(/^```(\w*)/);

                    if (slideMatch) { // Start a new slide
                        slideFound = true; // Mark that we found at least one slide
                        expectingTitleText = false;
                        currentSlideDiv = document.createElement('div');
                        currentSlideDiv.classList.add('slide-container');
                        const titleText = (slideMatch[1] || "").trim();
                        if (titleText) {
                            const title = document.createElement('h3');
                            title.classList.add('slide-title');
                            title.textContent = titleText;
                            currentSlideDiv.appendChild(title);
                        }
                        currentPointsList = document.createElement('ul');
                        currentPointsList.classList.add('slide-points');
                        currentSlideDiv.appendChild(currentPointsList);
                        currentNotesDiv = null;
                        messageDiv.appendChild(currentSlideDiv);
                    } else if (titleMarkerMatch && currentSlideDiv && !currentSlideDiv.querySelector('.slide-title')) {
                        expectingTitleText = true; // Expect title on next relevant line
                    } else if (expectingTitleText && currentSlideDiv && !pointMatch && !notesMatch && !line.startsWith('- ') && line.length > 0 && !line.startsWith('```')) {
                         const title = document.createElement('h3');
                         title.classList.add('slide-title');
                         title.textContent = line;
                         if(currentPointsList) {
                            currentSlideDiv.insertBefore(title, currentPointsList);
                         } else {
                             currentSlideDiv.appendChild(title);
                         }
                         expectingTitleText = false; // Title found
                     }
                    else if (pointMatch && currentSlideDiv && currentPointsList) { // Found bullet point
                        expectingTitleText = false; // Not expecting title anymore
                        const li = document.createElement('li');
                        const boldPart = pointMatch[1].trim(); // "**Point**"
                        const restPart = (pointMatch[2] || "").trim(); // Rest
                        li.innerHTML = `<strong>${boldPart.slice(2,-2)}:</strong> ${restPart}`;
                        currentPointsList.appendChild(li);
                    } else if (notesMatch && currentSlideDiv) { // Found speaker notes
                        expectingTitleText = false;
                        currentNotesDiv = document.createElement('div');
                        currentNotesDiv.classList.add('speaker-notes');
                        const notesTitle = document.createElement('strong');
                        notesTitle.textContent = "Speaker Notes:";
                        currentNotesDiv.appendChild(notesTitle);
                        const notesContent = document.createElement('p');
                        notesContent.textContent = notesMatch[1].trim();
                        currentNotesDiv.appendChild(notesContent);
                        currentSlideDiv.appendChild(currentNotesDiv);
                    } else if (line.startsWith('```') && currentSlideDiv) {
                        // --- Code Block Handling (Keep simple for PPT context) ---
                        expectingTitleText = false;
                         let codeContent = line + '\n';
                         let currentLineIndex = lines.findIndex(l => l.trim() === line);
                         let nextIndex = currentLineIndex + 1;
                         while(nextIndex < lines.length && !lines[nextIndex].trim().endsWith('```')) {
                             codeContent += lines[nextIndex] + '\n';
                             nextIndex++;
                         }
                         if (nextIndex < lines.length) codeContent += lines[nextIndex];

                         const languageMatch = codeContent.match(/^```(\w+)?\s*\n?/);
                         const language = languageMatch && languageMatch[1] ? languageMatch[1].toLowerCase() : 'plaintext';
                         const actualCode = codeContent.substring(languageMatch ? languageMatch[0].length : 3, codeContent.lastIndexOf('```')).trim();

                         const pre = document.createElement('pre');
                         const code = document.createElement('code');
                         code.classList.add(`language-${language}`);
                         code.textContent = actualCode;
                         pre.appendChild(code);
                         currentSlideDiv.appendChild(pre); // Append code block to current slide
                         // Skip copy button for PPT context unless needed
                         // Apply highlighting later
                        // --- End Code Block ---
                     } else if (currentNotesDiv) { // Continue speaker notes
                        expectingTitleText = false;
                         const p = document.createElement('p');
                         p.textContent = line;
                         currentNotesDiv.appendChild(p);
                     }
                     // Ignore other lines
                });

                // Fallback: If parsing failed to create any slides, show raw text
                if (!slideFound) {
                     console.warn("PPT Parsing failed to find slide structure. Displaying raw text.");
                     const pre = document.createElement('pre');
                     pre.style.whiteSpace = 'pre-wrap';
                     pre.textContent = text;
                     messageDiv.appendChild(pre);
                     // Still show PPTX button in fallback, PHP script might handle raw text okay
                     const pptxButtonContainer = document.getElementById('pptx-button-container');
                     if (pptxButtonContainer) pptxButtonContainer.style.display = 'block';
                     const pptxStatus = document.getElementById('pptx-status');
                     if(pptxStatus) pptxStatus.innerHTML = '';
                } else {
                    // Parsing likely succeeded, show PPTX button
                     const pptxButtonContainer = document.getElementById('pptx-button-container');
                     if (pptxButtonContainer) pptxButtonContainer.style.display = 'block';
                     const pptxStatus = document.getElementById('pptx-status');
                      if(pptxStatus) pptxStatus.innerHTML = ''; // Clear previous status
                }

                 // Apply highlighting after structure is built
                 messageDiv.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightElement(block);
                 });

            } else {
                messageDiv.textContent = text; // User message is plain text
            }

            chatBox.appendChild(messageDiv); // Add the message bubble to the chatbox
            void messageDiv.offsetWidth; // Trigger CSS animation
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the bottom
        }

        // Function to fetch AI response (for text content)
        async function fetchMessage(history) {
            const chatBox = document.getElementById('chat-messages'); // Get chatBox inside function
            const sendBtn = document.getElementById('send-btn');
            const userInput = document.getElementById('user-input');
             const pptxButtonContainer = document.getElementById('pptx-button-container');

             if (!chatBox || !sendBtn || !userInput || !pptxButtonContainer) {
                 console.error("Required elements not found in fetchMessage");
                 return;
             }

            // Show loading indicator
            const thinkingDiv = document.createElement('div');
            thinkingDiv.classList.add('message', 'bot');
            thinkingDiv.textContent = '...';
            chatBox.appendChild(thinkingDiv);
            void thinkingDiv.offsetWidth;
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                // Send request to the backend API (reusing code assistant endpoint)
                const response = await fetch('../../backend/api/handle_code_assistant.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: history }) // Send the conversation history
                });
                // Check if the network response is ok
                if (!response.ok) throw new Error(`Network response was not ok (${response.status})`);
                const data = await response.json(); // Parse the JSON response

                if (chatBox && thinkingDiv.parentNode === chatBox) { // Check before removing
                    chatBox.removeChild(thinkingDiv); // Remove loading indicator
                }

                // Process the AI's reply
                if (data.reply && data.reply.toLowerCase().startsWith("error:")) {
                    appendMessage('bot', data.reply); // Display error from AI
                    pptxButtonContainer.style.display = 'none'; // Hide button on error
                } else if(data.reply) {
                    appendMessage('bot', data.reply); // Display successful reply
                    // Update the correct global history
                    window.chatHistory.push({ role: 'assistant', content: data.reply });
                } else {
                    appendMessage('bot', 'Sorry, I received an empty response.'); // Handle empty reply
                     pptxButtonContainer.style.display = 'none'; // Hide button on empty
                }
            } catch (error) { // Handle network errors or JSON parsing errors
                console.error('Fetch error:', error);
                 if (chatBox && thinkingDiv.parentNode === chatBox) { // Check before removing
                    chatBox.removeChild(thinkingDiv); // Remove loading indicator
                 }
                appendMessage('bot', `Sorry, something went wrong. ${error.message}`); // Display error
                pptxButtonContainer.style.display = 'none'; // Hide button on error
            } finally {
                 // Re-enable input form
                 sendBtn.disabled = false;
                 userInput.disabled = false;
            }
        }

        // --- Wait for DOM Ready ---
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to DOM elements used in listeners
            const chatForm = document.getElementById('chat-input-form');
            const userInput = document.getElementById('user-input');
            const generatePptxBtn = document.getElementById('generate-pptx-btn');
            const pptxStatus = document.getElementById('pptx-status');

             // Ensure elements exist before adding listeners
             if (!chatForm || !userInput || !generatePptxBtn || !pptxStatus) {
                 console.error("Form or PPTX button elements not found!");
                 return;
             }

             // Make chatHistory global for easier access
             window.chatHistory = [
                { role: 'system', content: 'You are a presentation content generator. Create detailed content for each slide based on the user topic. Structure the response clearly. Use markdown like ### Slide Title:, #### Title:, - **Point:** details..., and **Speaker Note:** details.... Aim for 5-7 slides unless specified otherwise. Do not add intro/outro slides, just the core content slides.' },
                { role: 'assistant', content: 'Hello! What topic would you like me to generate presentation content for?' }
            ];

            // Event listener for form submission (getting the text content)
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const messageText = userInput.value.trim();
                if (messageText === "") return;

                // Disable button during generation
                const sendBtn = document.getElementById('send-btn'); // Get inside listener
                if(sendBtn) sendBtn.disabled = true;
                userInput.disabled = true;
                const pptxButtonContainer = document.getElementById('pptx-button-container');
                if(pptxButtonContainer) pptxButtonContainer.style.display = 'none';
                window.lastBotReplyText = ""; // Clear previous content

                appendMessage('user', `Generate presentation content for: ${messageText}`);
                window.chatHistory.push({ role: 'user', content: `Generate presentation content for the topic: ${messageText}` });
                userInput.value = "";
                fetchMessage(window.chatHistory); // Pass global history
            });

            // Event listener for the "Generate PPTX" button
            generatePptxBtn.addEventListener('click', async function() {
                if (!window.lastBotReplyText) { // Access global variable
                    pptxStatus.textContent = "Error: No presentation content available to generate.";
                    return;
                }

                // Find topic from global history
                const topic = window.chatHistory.findLast(msg => msg.role === 'user')?.content.replace('Generate presentation content for the topic: ','') || 'presentation';

                generatePptxBtn.disabled = true;
                pptxStatus.innerHTML = '<div class="loader-ppt">Generating PPTX file...</div>';
                let responseText = '';

                try {
                    const response = await fetch('../../backend/api/handle_ppt_generate_file.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ topic: topic, raw_content: window.lastBotReplyText }) // Use global variable
                    });

                    responseText = await response.text();
                    console.log("Raw Response Text (PPTX Gen):", responseText);
                    const data = JSON.parse(responseText);

                    if (response.ok && data.status === 'success' && data.filename) {
                        const downloadUrl = `../../backend/temp/${data.filename}`;
                        const linkHTML = `
                            <a href="${downloadUrl}" download="${data.filename || topic + '.pptx'}" class="btn-tool" style="margin-top: 1rem; display: inline-block;">
                                Download Presentation (.pptx)
                            </a>
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                (File: ${data.filename})
                            </p>`;
                        pptxStatus.innerHTML = linkHTML;
                        console.log("Download link created for:", data.filename);
                    } else {
                        pptxStatus.innerHTML = `<p style="color: var(--error-color);">Error: ${data.message || 'Unknown error generating PPTX.'}</p>`;
                        console.error("PPTX Generation failed. Data:", data);
                    }
                } catch (error) {
                    console.error('PPTX Generation error:', error);
                    console.error("Failed to process response. Raw text was:", responseText);
                    pptxStatus.innerHTML = `<p style="color: var(--error-color);">Error: Invalid response from generation server.</p>`;
                } finally {
                    generatePptxBtn.disabled = false;
                     // setTimeout(() => { if(pptxStatus) pptxStatus.innerHTML = ''; }, 7000);
                }
            });
        }); // End DOMContentLoaded

    </script>

</body>
</html>