<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login | JoeBean</title>
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/cashier/cashier_logins.css">
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
                <form class="CashierLogin__form">
                    <div class="CashierLogin__input-group">
                        <input type="text" placeholder="" required />
                        <label>Username</label>
                        <img
                            class="CashierLogin__username-icon"
                            src="../../assets/images/username-icon.svg"
                            alt="username-icon" />
                    </div>
                    <div class="CashierLogin__input-group margin-none">
                        <input type="password" placeholder="" required />
                        <label>Password</label>
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
                    <button type="button" class="CashierLogin__home-button">
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