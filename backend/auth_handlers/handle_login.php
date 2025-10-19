<?php
// Start session
session_start();

// Include database connection
require_once "../config/db.php";

// Define variables
$email = $password = "";
$login_err = "";

// Process data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // --- 1. Validate Email ---
    if(empty(trim($_POST["email"]))){
        $login_err = "Invalid email or password.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // --- 2. Validate Password ---
    if(empty(trim($_POST["password"]))){
        $login_err = "Invalid email or password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // --- 3. Check Database ---
    if(empty($login_err)){
        $sql = "SELECT id, email, password_hash, role FROM users WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $param_email = $email;
            
            if($stmt->execute()){
                // Check if email exists
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $hashed_password = $row["password_hash"];
                        $role = $row["role"];
                        
                        // --- 4. Verify Password ---
                        if(password_verify($password, $hashed_password)){
                            // Password is correct! Start new session.
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["role"] = $role; // <-- This is crucial
                            
                            // Redirect user to the main router
                            // The index.php file will handle the role-based redirect
                            header("location: ../../index.php");
                            exit;
                        } else {
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            unset($stmt);
        }
    }
    
    // --- 5. Handle Failure ---
    if(!empty($login_err)){
        // Redirect back to login page with an error
        header("location: ../../auth/login.php?status=login_failed");
        exit;
    }
    
    unset($pdo);
} else {
    // If accessed directly, send to login
    header("location: ../../auth/login.php");
    exit;
}
?>