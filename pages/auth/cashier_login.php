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
        // First check if the account is soft-deleted
        if ($user['deleted_at'] !== null) {
            $error_message = "This account has been deactivated.";
        }
        // If not soft-deleted, continue with password verification
        else if (password_verify($password, $user['password'])) {
            // Check if the user's role is 'cashier'
            if ($user['role'] === 'cashier') {
                // Set session or cookie for logged-in user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to the cashier dashboard or home page
                header("Location: cashier_dashboard.php");
                exit;
            } else {
                $error_message = "You are not authorized as a cashier.";
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
    <title>Cashier Login | JoeBean</title>
    <link rel="stylesheet" href="../../assets/css/indexs.css">
    <link rel="stylesheet" href="../../assets/css/cashier/cashier_login.css">
</head>

<body>
    <div class="CashierLogin__main-content">
        <div class="CashierLogin__left-container">
            <div class="CashierLogin__branding">
                <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="CashierLogin__logo" />
                <div class="CashierLogin__system-name">
                    <p class="CashierLogin__title">JoeBean</p>
                    <p class="CashierLogin__subtitle">Point-of-Sale System with Inventory</p>
                </div>
            </div>

            <div class="CashierLogin__form-container">
                <p class="CashierLogin__form-type">Cashier</p>
                <h2>Login</h2>
                <form class="CashierLogin__form" method="post">
                    <div class="CashierLogin__input-group">
                        <input type="text" name="username" id="username" placeholder="" autocomplete="off" required />
                        <label for="username">Username</label>
                        <img
                            class="CashierLogin__username-icon"
                            src="../../assets/images/username-icon.svg"
                            alt="username-icon" />
                        <span class="error-message">
                            <?= $error_message; ?>
                        </span>
                    </div>
                    <div class="CashierLogin__input-group margin-none">
                        <input type="password" name="password" id="password" placeholder="" autocomplete="off" required />
                        <label for="password">Password</label>
                        <img
                            class="CashierLogin__password-icon"
                            src="../../assets/images/password-icon.svg"
                            alt="username-icon" />
                        <img
                            class="CashierLogin__eye-icon"
                            src="../../assets/images/eye-close-icon.svg"
                            alt="eye password" />
                    </div>
                    <a href="#" class="CashierLogin__forgot-password">Forgot Password?</a>
                    <button type="submit" class="CashierLogin__home-button">
                        Login
                    </button>
                    <button type="button" class="CashierLogin__type-button">
                        Admin
                    </button>
                </form>
                <p class="CashierLogin__redirect-text">
                    Don't have an account yet? <a href="cashier_register.php" class="CashierLogin__register-link">Register here</a>
                </p>
            </div>
        </div>

        <div class="CashierLogin__right-container">
            <img src="../../assets/images/cashier-login-icon.png" alt="Login Image" />
        </div>
    </div>

    <script src="../../assets/js/cashier/cashier_login.js"></script>
</body>

</html>