<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Master AI Hub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-wrapper">
    <div class="auth-container">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php
        if(isset($_GET['status']) && $_GET['status'] == 'reg_success'){
            echo "<div class='message success'>Registration successful! Please login.</div>";
        }
        if(isset($_GET['status']) && $_GET['status'] == 'login_failed'){
            echo "<div class='message error'>Invalid email or password.</div>";
        }
        ?>

        <form action="../backend/auth_handlers/handle_login.php" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Login">
            </div>
            <p class="auth-link">Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
        </div>
    </div>
</body>
</html>