<?php
// Start session
session_start();

// 1. Security Check: Must be a logged-in admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../auth/login.php");
    exit;
}

// 2. Include database
require_once "../config/db.php";

// 3. Check if a user ID was posted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])){
    
    $user_id_to_delete = $_POST['user_id'];

    // 4. Critical Security Check: Admin cannot delete themselves!
    if($user_id_to_delete == $_SESSION["id"]){
        // Redirect back with an error
        header("location: ../../app/admin.php?status=error_self_delete");
        exit;
    }

    // 5. Proceed with deletion
    try {
        $sql = "DELETE FROM users WHERE id = :id";
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":id", $user_id_to_delete, PDO::PARAM_INT);
            $stmt->execute();
            
            // Redirect back with success
            header("location: ../../app/admin.php?status=user_deleted");
            exit;
        }
    } catch(PDOException $e){
        // Redirect back with a generic error
        header("location: ../../app/admin.php?status=error_db");
        exit;
    }

    unset($stmt);
    unset($pdo);

} else {
    // If accessed improperly, send back to admin panel
    header("location: ../../app/admin.php");
    exit;
}
?>