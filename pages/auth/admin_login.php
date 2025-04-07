<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/index.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_logins.css">
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
                    <form class="AdminLogin__form">
                        <div class="AdminLogin__input-group">
                            <input type="text" placeholder="" required />
                            <label>Username</label>
                            <img
                                class="AdminLogin__username-icon"
                                src="../../assets/images/username-icon.svg"
                                alt="username-icon"
                            />
                        </div>
                        <div class="AdminLogin__input-group margin-none">
                            <input type="password" placeholder="" required />
                            <label>Password</label>
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
                        <button type="button" class="AdminLogin__home-button">
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