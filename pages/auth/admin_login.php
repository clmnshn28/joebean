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
                header("Location:  ../admin/admin_item_list.php");
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
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_logini.css">
        <link rel="stylesheet" href="../../assets/css/modall.css">
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
                        <a class="AdminLogin__forgot-password">Forgot Password?</a>
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

        <!-- modal -->
        <div class="modal" id="forgotPasswordModal"  style="display: none;">
            <div class="Modal_fade-in AdminLogin__modal-forgot-content">
                <span class="AdminLogin__modal-close">&times;</span>
                 
                <!-- Step 1: Request OTP Form via Email (Initially visible) -->
                <div id="step1" class="reset-step">
                    <div class="AdminLogin__modal-step-indicator">
                        <div class="AdminLogin__modal-step active"></div>
                        <div class="AdminLogin__modal-step"></div>
                        <div class="AdminLogin__modal-step"></div>
                    </div>
                    <h3 class="AdminLogin__modal-title">Recover Your Password</h3>
                    <p class="AdminLogin__modal-description">Enter your email address in your account and we'll  send a  confirmation  code  to reset your password.</p>
                    <form class="AdminLogin__modal-form" id="requestOtpForm">
                        <div class="AdminLogin__modal-input-group">
                            <input type="email" name="reset_email" id="reset_email" placeholder="" autocomplete="off" required />
                            <label for="reset_email">Email</label>
                            <span class="AdminLogin__modal-error-message">fijadifjfo</span>
                        </div>
                        <button type="submit" class="AdminLogin__modal-send-otp-button">Send OTP</button>
                    </form>
                 
                </div>
                
                <!-- Step 2: Verify OTP Form (Initially hidden) -->
                <div id="step2" class="reset-step" style="display: none;">
                    <div class="AdminLogin__modal-step-indicator">
                        <div class="AdminLogin__modal-step completed"></div>
                        <div class="AdminLogin__modal-step active"></div>
                        <div class="AdminLogin__modal-step"></div>
                    </div>
                    <h3 class="AdminLogin__modal-title">Enter Confirm Code</h3>
                    <p class="AdminLogin__modal-verify-description">Enter the 6-digit code we sent to <span id="emailDisplay">ce****n@gmail.com</span></p>
                    
                    <form class="AdminLogin__modal-form" id="verifyOtpForm">
                        <div class="AdminLogin__modal-otp-timer">OTP expires in: <span id="otpTimer">05:00</span><button type="button" id="resendOtp" class="AdminLogin__resend-button">Resend OTP</button> </div>
                        <div class="AdminLogin__modal-otp-container">
                            <input type="text" maxlength="1" class="AdminLogin__modal-otp-input" required />
                            <input type="text" maxlength="1" class="AdminLogin__modal-otp-input" required />
                            <input type="text" maxlength="1" class="AdminLogin__modal-otp-input" required />
                            <input type="text" maxlength="1" class="AdminLogin__modal-otp-input" required />
                            <input type="text" maxlength="1" class="AdminLogin__modal-otp-input" required />
                            <input type="text" maxlength="1" class="AdminLogin__modal-otp-input" required />
                            <input type="hidden" id="full_otp" name="otp_code" />
                            <span class="AdminLogin__modal-error-otp-message">fijadifjfo</span>
                        </div>
                        <button type="submit" class="AdminLogin__modal-send-otp-button">Verify OTP</button>
                        <button type="button" id="backButton2" class="AdminLogin__back-button">
                            <img src="../../assets/images/arrow-left.svg" alt="arrow left">
                            Go Back
                        </button>
                    </form>
                </div>
                
                <!-- Step 3: Change Password Form (Initially hidden) -->
                <div id="step3" class="reset-step" style="display: none;">
                    <div class="AdminLogin__modal-step-indicator">
                        <div class="AdminLogin__modal-step completed"></div>
                        <div class="AdminLogin__modal-step completed"></div>
                        <div class="AdminLogin__modal-step active"></div>
                    </div>
                    <h3 class="AdminLogin__modal-title">Reset Your Password</h3>
                    <form class="AdminLogin__modal-form AdminLogin__modal-space-top" id="changePasswordForm">
                        <div class="AdminLogin__modal-input-group">
                            <input type="password" name="new_password" id="new_password" placeholder="" autocomplete="off" required />
                            <label for="new_password">New Password</label>
                            <span class="AdminLogin__modal-error-otp-message">fijadifjfo</span>
                        </div>
                        <div class="AdminLogin__modal-input-group">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="" autocomplete="off" required />
                            <label for="confirm_password">Confirm New Password</label>
                        </div>
                        <div class="CashierRegister__password-requirements">
                            <p>Your password must include the following:</p>
                            <ul>
                                <li id="lengthRequirement"><span class="wrong">&#10005;</span> Be 8–100 characters long</li>
                                <li id="caseRequirement"><span class="wrong">&#10005;</span> Contain at least one uppercase and one lowercase letter</li>
                                <li id="specialRequirement"><span class="wrong">&#10005;</span> Contain at least one number or special character</li>
                            </ul>
                        </div>

                        <button type="submit" class="AdminLogin__reset-pass-button">Reset Password</button>
                        <button type="button"  id="backButton3" class="AdminLogin__back-button">
                            <img src="../../assets/images/arrow-left.svg" alt="arrow left">
                            Go Back
                        </button>
                    </form>
                </div>
                
                <!-- Success message (shown after completion) -->
                <div id="successStep" class="reset-step AdminLogin__modal-reset-success-container" style="display: none;">
                    <img class="AdminLogin__modal-success-icon" src="../../assets/images/successful-icon.svg" alt="">
                    <h3 class=" AdminLogin__modal-title">Password Reset Successful!</h3>
                    <p class=" AdminLogin__success-message">Your password has been changed successfully.</p>
                    <button type="button" id="returnToLogin" class="AdminLogin__modal-send-otp-button">Return to Login</button>
                </div>
                
           
            </div>
        </div>
        
        <script src="../../assets/js/admin/admin_loginb.js"></script>
    </body>
</html>