<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Master AI Hub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-wrapper">
    <div class="auth-container">
        <h2>Sign Up</h2>
        <p>Requires @adhiyamaan.in email</p>

        <form action="../backend/auth_handlers/handle_register.php" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-submit" value="Submit">
            </div>
            <p class="auth-link">Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
      </div>
    </div>
</body>
</html>