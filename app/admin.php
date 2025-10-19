<?php
// Start session
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../auth/login.php");
    exit;
}
require_once "../backend/config/db.php";
$users = [];
$error_message = "";
$success_message = "";
if(isset($_GET['status'])){
    if($_GET['status'] == 'user_deleted'){ $success_message = "User successfully deleted."; }
    if($_GET['status'] == 'error_self_delete'){ $error_message = "Error: You cannot delete your own account!"; }
    if($_GET['status'] == 'error_db'){ $error_message = "Error: A database error occurred."; }
    if($_GET['status'] == 'error_no_id'){ $error_message = "Error: No user ID was provided."; }
}
try {
    $sql = "SELECT id, email, role, created_at FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e){
    $error_message = "Error: Could not fetch users. " . $e->getMessage();
}
unset($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - User Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { width: 90%; max-width: 1000px; margin: auto; }
        h1, h2 { color: #333; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .message_error { padding: 10px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .message_success { padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
        .action-btn {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            border: none;
            margin-right: 5px;
        }
        .btn-history { background: #007bff; }
        .btn-delete { background: #dc3545; }
        .action-cell { min-width: 150px; } /* Give more space for buttons */
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Control Panel</h1>
        <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION["email"]); ?></strong>!</p>
        <p><a href="../backend/auth_handlers/handle_logout.php">Sign Out of Your Account</a></p>
        <hr>
        <h2>User Management</h2>
        
        <?php if(!empty($success_message)){ echo "<div class='message_success'>$success_message</div>"; } ?>
        <?php if(!empty($error_message)){ echo "<div class='message_error'>$error_message</div>"; } ?>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered On</th>
                    <th>Actions</th> </tr>
            </thead>
            <tbody>
                <?php
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                        
                        // --- NEW: Actions Cell ---
                        echo "<td class='action-cell'>";
                        
                        // 1. View History Button (links to our new page)
                        echo "<a href='user_history.php?user_id=" . $user['id'] . "' class='action-btn btn-history'>View History</a>";

                        // 2. Delete Button
                        if($user['id'] == $_SESSION['id']){
                            echo "<i>(This is you)</i>";
                        } else {
                            echo "<form action='../backend/auth_handlers/handle_delete_user.php' method='POST' onsubmit=\"return confirm('Are you sure you want to delete this user?');\" style='display:inline;'>";
                            echo "<input type='hidden' name='user_id' value='" . $user['id'] . "'>";
                            echo "<button type='submit' class='action-btn btn-delete'>Delete</button>";
                            echo "</form>";
                        }
                        echo "</td>";
                        // --- END NEW ---
                        
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>