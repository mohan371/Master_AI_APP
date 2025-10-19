<?php
// Start the session
session_start();
 
// Check if the user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    
    // --- Role-Based Redirect ---
    // If the user is an admin, send to admin page
    if($_SESSION["role"] == "admin"){
        header("location: app/admin.php");
    } else {
        // Otherwise, send to standard student dashboard
        header("location: app/dashboard.php");
    }
    exit;

} else {
    // If not logged in, redirect to the login page
    header("location: auth/login.php");
    exit;
}
?>