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
    <title>Image Generator - Master AI Hub</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="main-container">
        <header class="main-header">
            <a href="../dashboard.php" class="logo">Master AI Hub</a>
            <div class="user-menu">
                <a href="../dashboard.php">← Back to Hub</a>
                <a href="../../backend/auth_handlers/handle_logout.php">Sign Out</a>
            </div>
        </header>

        <main class="tool-content-wrapper">
            <h1 style="text-align: center;">🎨 Image Generator</h1>
            <p style="text-align: center; margin-top: -1rem; margin-bottom: 2rem; color: var(--text-secondary);">Type a prompt to generate a new image.</p>

            <div class="image-gen-container">
                <div class="chat-input-area" style="padding: 0 0 2rem 0;"> 
                    <form class="input-form" id="image-form"> 
                        <input type="text" id="prompt-input" placeholder="e.g., A red cat in a blue hat..." autocomplete="off">
                        <button type="submit" id="generate-btn">➤</button> 
                    </form>
                </div>
                <div id="image-display-area">
                    <div class="loader" id="loader" style="display: none;"></div>
                    <p id="status-message">Your generated image will appear here.</p>
                    <img id="generated-image" class="generated-image" alt="Generated image">
                </div>
            </div>
        </main>
    </div>

    <script>
        // Your JavaScript code here (it's the same as before, no changes)
        const imageForm = document.getElementById('image-form');
        const promptInput = document.getElementById('prompt-input');
        const generateBtn = document.getElementById('generate-btn'); // Still needed for disable/enable
        const displayArea = document.getElementById('image-display-area');
        const loader = document.getElementById('loader');
        const statusMessage = document.getElementById('status-message');
        const generatedImage = document.getElementById('generated-image');

        imageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const prompt = promptInput.value.trim();
            if (prompt === "") return;

            loader.style.display = 'block';
            statusMessage.textContent = 'Generating... This can take up to a minute.';
            generatedImage.style.display = 'none';
            generateBtn.disabled = true;
            promptInput.disabled = true; // Also disable input

            try {
                const response = await fetch('../../backend/api/handle_image_gen.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    generatedImage.src = 'data:image/jpeg;base64,' + data.base64_image;
                    generatedImage.style.display = 'block';
                    statusMessage.textContent = 'Generation successful!';
                } else {
                    statusMessage.textContent = 'Error: ' + data.message;
                }
            } catch (error) {
                console.error('Fetch error:', error);
                statusMessage.textContent = 'Error: Could not connect to the server.';
            } finally {
                loader.style.display = 'none';
                generateBtn.disabled = false;
                promptInput.disabled = false; // Re-enable input
            }
        });
    </script>
</body>
</html>