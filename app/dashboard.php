<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../auth/login.php");
    exit;
}
if($_SESSION["role"] == 'admin'){
    header("location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master AI Hub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container">
        <header class="main-header">
            <a href="dashboard.php" class="logo">Master AI Hub</a>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
                <a href="../backend/auth_handlers/handle_logout.php">Sign Out</a>
            </div>
        </header>

        <main class="tool-content-wrapper">
            <h1 style="margin-bottom: 1rem;">Welcome to the Hub</h1>
            <p style="margin-bottom: 2rem; font-size: 1.1rem; color: var(--text-secondary);">Select a tool to get started.</p>

            <div class="tool-grid">
                <div class="tool-card">
                    <h3>🤖 AI Chatbot</h3>
                    <p>A powerful, conversational AI with memory.</p>
                    <a href="tools/chat.php" class="btn-tool">Open Chat</a>
                </div>

                <div class="tool-card">
                    <h3>🎨 Image Generator</h3>
                    <p>Create stunning images from text descriptions.</p>
                    <a href="tools/image_gen.php" class="btn-tool">Open Generator</a>
                </div>

                <div class="tool-card">
    <h3>💻 Code Assistant</h3>
    <p>Get help writing, debugging, and explaining code.</p>
    <a href="tools/code_assistant.php" class="btn-tool">Open Assistant</a>
</div>

               <div class="tool-card">
    <h3>📊 PPT Generator</h3>
    <p>Generate presentation slides on any topic.</p>
    <a href="tools/ppt_generator.php" class="btn-tool">Open Generator</a>
</div>
            </div>
        </main>
    </div>
</body>
</html>