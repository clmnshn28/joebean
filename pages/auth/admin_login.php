<?php

include '../../config/db.php';

session_start();

$error_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    $checkStmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    // Fetch the actual user data from the result
    $user = $result->fetch_assoc();

    // Check if the user exists
    if ($user) {

        // Check if the password is correct
        if (password_verify($password, $user['password'])) {
            // Check if the user's role is 'cashier'
            if ($user['role'] === 'admin') {
                // Set session or cookie for logged-in user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to the cashier dashboard or home page
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error_message = "You are not authorized as an admin.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/index.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_login.css">
    </head>
    <body>
    <div class="AdminLogin__main-content">
            <div class="AdminLogin__left-container">
                <div class="AdminLogin__branding">
                    <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="AdminLogin__logo" />
                    <div class="AdminLogin__system-name">
                        <p class="AdminLogin__title">JoeBean</p>
                        <p class="AdminLogin__subtitle">Point-of-Sale System with Inventory</p>
                    </div>
                </div>

                <div class="AdminLogin__form-container">
                    <p>admin</p>
                    <h2>Login</h2>
                    <form class="AdminLogin__form" method="post">
                        <div class="AdminLogin__input-group">
                            <input type="text" name="username" id="username" placeholder="" autocomplete="off" required />
                            <label for="username">Username</label>
                            <img
                                class="AdminLogin__username-icon"
                                src="../../assets/images/username-icon.svg"
                                alt="username-icon"
                            />
                            <span class="error-message">
                                <?= $error_message; ?>
                            </span>
                        </div>
                        <div class="AdminLogin__input-group margin-none">
                            <input type="password" name="password" id="password" placeholder="" autocomplete="off" required />
                            <label for="password">Password</label>
                            <img
                                class="AdminLogin__password-icon"
                                src="../../assets/images/password-icon.svg"
                                alt="username-icon"
                            />
                            <img
                                class="AdminLogin__eye-icon"
                                src="../../assets/images/eye-close-icon.svg"
                                alt="eye password"
                            />
                        </div>
                        <a href="#" class="AdminLogin__forgot-password">Forgot Password?</a>
                        <button type="submit" class="AdminLogin__home-button">
                            Login
                        </button>
                        <button type="button" class="AdminLogin__type-button">
                            Cashier
                        </button>
                    </form>
                    <p class="AdminLogin__redirect-text">
                        Don't have an account yet? <a href="SignUp.html" class="AdminLogin__register-link">Register here</a>
                    </p>
                </div>
            </div>
            
            <div class="AdminLogin__right-container">
                <img src="../../assets/images/admin-login-icon.png" alt="Login Image" />
            </div>
        </div>
        
        <script src="../../assets/js/admin/admin_login.js"></script>
    </body>
</html>