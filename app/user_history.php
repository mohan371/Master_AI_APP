<?php
// Start session
session_start();

// 1. Security Check: Must be a logged-in admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../auth/login.php");
    exit;
}

// 2. Check if a user ID was provided in the URL
if(!isset($_GET['user_id']) || empty($_GET['user_id'])){
    header("location: admin.php?status=error_no_id");
    exit;
}

$user_id = $_GET['user_id'];
$user_email = "Unknown User";
$chat_history = [];
$error_message = "";

// 3. Include database
require_once "../backend/config/db.php";

try {
    // 4. Get the user's email to display
    $sql_user = "SELECT email FROM users WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute(['user_id' => $user_id]);
    
    if($stmt_user->rowCount() == 1){
        $user_email = $stmt_user->fetch(PDO::FETCH_ASSOC)['email'];
    } else {
        throw new Exception("User not found.");
    }

    // 5. Get all chat history for this user
    $sql_history = "SELECT role, content, timestamp FROM chat_history WHERE user_id = :user_id ORDER BY timestamp ASC";
    $stmt_history = $pdo->prepare($sql_history);
    $stmt_history->execute(['user_id' => $user_id]);
    
    $chat_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

unset($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat History - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Make this page use the full width */
        body {
            align-items: flex-start;
        }
        .history-container {
            width: 100%;
            max-width: 900px;
            margin: 20px;
            text-align: left;
        }
        .history-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .history-header h1 { margin: 0; }
        .chat-box {
            height: 60vh; /* Make the chat box a bit shorter */
        }
    </style>
</head>
<body>
    <div class="history-container">
        <div class="history-header">
            <h1>Chat History</h1>
            <p>Viewing all messages for user: <strong><?php echo htmlspecialchars($user_email); ?></strong></p>
            <a href="admin.php">← Back to Admin Panel</a>
        </div>

        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php else: ?>
            <div class="chat-container" style="margin: 0;">
                <div class="chat-box">
                    <?php if (count($chat_history) > 0): ?>
                        <?php foreach ($chat_history as $message): ?>
                            <div class="message <?php echo ($message['role'] == 'user') ? 'user' : 'bot'; ?>">
                                <?php echo htmlspecialchars($message['content']); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="message bot">No chat history found for this user.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>