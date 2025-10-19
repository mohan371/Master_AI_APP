<?php
// Include our database connection
require_once "../config/db.php";

// Define variables
$email = $password = $confirm_password = "";
$required_domain = "@adhiyamaan.in";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // --- 1. Validate Email ---
    if(empty(trim($_POST["email"]))){
        die("Error: Please enter an email.");
    } else {
        $email = trim($_POST["email"]);
        
        // --- 2. Check Domain ---
        if(substr($email, -strlen($required_domain)) !== $required_domain){
            die("Error: Registration is only allowed for @adhiyamaan.in domains.");
        }
        
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = :email";
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $param_email = $email;
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    die("Error: This email is already taken.");
                }
            } else {
                die("Oops! Something went wrong. Please try again later.");
            }
            unset($stmt);
        }
    }
    
    // --- 3. Validate Password ---
    if(empty(trim($_POST["password"]))){
        die("Error: Please enter a password.");     
    } elseif(strlen(trim($_POST["password"])) < 6){
        die("Error: Password must have at least 6 characters.");
    } else {
        $password = trim($_POST["password"]);
    }
    
    // --- 4. Validate Confirm Password ---
    if(empty(trim($_POST["confirm_password"]))){
        die("Error: Please confirm password.");     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if($password != $confirm_password){
            die("Error: Passwords did not match.");
        }
    }
    
    // --- 5. Insert into database ---
    // (We only get here if all previous checks pass)
    
    $sql = "INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)";
     
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
        $stmt->bindParam(":password_hash", $param_password_hash, PDO::PARAM_STR);
        
        $param_email = $email;
        $param_password_hash = password_hash($password, PASSWORD_DEFAULT); // Hashes the password
        
        if($stmt->execute()){
            // Redirect to login page with a success message
            header("location: ../../auth/login.php?status=reg_success");
            exit;
        } else {
            echo "Something went wrong. Please try again later.";
        }
        unset($stmt);
    }
    
    // Close connection
    unset($pdo);
} else {
    // If someone tries to access this file directly, send them away
    header("location: ../../auth/register.php");
    exit;
}
?>