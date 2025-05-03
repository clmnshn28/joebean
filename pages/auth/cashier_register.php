<?php

include '../../config/db.php';

$usernameError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Securely hash password
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $birthDay = $_POST['day'];
    $birthMonth = $_POST['month'];
    $birthYear = $_POST['year'];
    $role = "cashier";
    
    // Function to get user ID by username
    function getUserId($conn, $username) {
        $result = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        return null;
    }
    $adminId = getUserId($conn, "admin");


    
    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $usernameError = "Username already exists.";
    }

    $checkStmt->close();

    // Check if email already exists
    $checkEmailStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        $emailError = "Email already exists.";
    }

    $checkEmailStmt->close();
    
    // Proceed only if username and email are unique
    if (empty($usernameError) && empty($emailError)) {
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../assets/images/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = time() . '_' . basename($_FILES['avatar']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($fileTmpPath, $targetPath)) {
                    $imagePath =  $fileName; 
                } else {
                    die("Image upload failed.");
                }
            } else {
                die("Invalid image type.");
            }
        } else {
            die("Image is required.");
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO users (username, password, firstname, lastname, middlename, email, gender, birth_day, birth_month, birth_year, image, role, admin_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssiiisss", $username, $password, $firstName, $lastName, $middleName, $email, $gender, $birthDay, $birthMonth, $birthYear, $imagePath, $role, $adminId);
    
        if ($stmt->execute()) {
            $registrationSuccess = true;
        } else {
            echo "Error: " . $stmt->error;
        }
    
        $stmt->close();
        $conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Register | JoeBean</title>
    <link rel="stylesheet" href="../../assets/css/indexs.css">
    <link rel="stylesheet" href="../../assets/css/cashier/cashier_registerer.css">
    <link rel="stylesheet" href="../../assets/css/modall.css">
</head>

<body>
    <div class="CashierRegister__main-content">
        <div class="CashierRegister__left-container">
            <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="CashierRegister__logo" />
        </div>
        <div class="CashierRegister__right-container">
            <div class="CashierRegister__branding">
                <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="CashierRegister__logo" />
                <div class="CashierRegister__system-name">
                    <p class="CashierRegister__title">JoeBean</p>
                    <p class="CashierRegister__subtitle">Point-of-Sale System with Inventory</p>
                </div>
            </div>


            <div class="CashierRegister__registration-container">
                <p>Cashier</p>
                <h2>Register</h2>

                <form class="CashierRegister__form-container" action="" method="POST" enctype="multipart/form-data">
                    <div class="CashierRegister__left-form">
                        <div class="CashierRegister__form-group">
                            <label for="firstName">
                                First Name
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="firstName" name="firstName" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" autocomplete="off" required>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label for="middleName">
                                Middle Name
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="middleName" name="middleName" value="<?php echo isset($_POST['middleName']) ? htmlspecialchars($_POST['middleName']) : ''; ?>" autocomplete="off" required>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label for="lastName">
                                Last Name
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="lastName" name="lastName" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" autocomplete="off" required>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label for="email">
                                Email
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" autocomplete="off" required>
                            <span class="error-username-message">
                                <?php if (!empty($emailError)) echo $emailError; ?>
                            </span>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label>
                                Gender
                                <span class="required">*</span>
                            </label>
                            <div class="CashierRegister__gender-group">
                                <div class="CashierRegister__gender-option">
                                    <input type="radio" id="male" name="gender" value="male" <?php echo isset($_POST['gender']) && $_POST['gender'] == 'male' ? 'checked' : ''; ?> required>
                                    <label for="male">Male</label>
                                </div>
                                <div class="CashierRegister__gender-option">
                                    <input type="radio" id="female" name="gender" value="female" <?php echo isset($_POST['gender']) && $_POST['gender'] == 'female' ? 'checked' : ''; ?> required>
                                    <label for="female">Female</label>
                                </div>
                            </div>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label for="birthday">
                                Birthday
                                <span class="required">*</span>
                            </label>
                            <div class="CashierRegister__birthday-group">
                                <input type="text" id="day" name="day" value="<?php echo isset($_POST['day']) ? htmlspecialchars($_POST['day']) : ''; ?>" placeholder="Day" autocomplete="off" required>
                                <input type="text" id="month" name="month" value="<?php echo isset($_POST['month']) ? htmlspecialchars($_POST['month']) : ''; ?>" placeholder="Month" autocomplete="off" required>
                                <input type="text" id="year" name="year" value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>" placeholder="Year" autocomplete="off" required>
                                <span class="error-message"></span>
                            </div>
                        </div>
                    </div>

                    <div class="CashierRegister__right-form">
                        <div class="CashierRegister__avatar-container">
                            <div class="CashierRegister__avatar-div">
                                <img id="preview" src="../../assets/images/profile-icon.svg" alt="image avatar" class="avatar-icon">
                            </div>
                            <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;">
                            <button type="button" class="CashierRegister__select-image-btn" onclick="document.getElementById('avatar').click();">SELECT IMAGE</button>
                            <span class="error-image-message"></span>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label for="username">
                                Username
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" autocomplete="off" required>
                            <span class="error-username-message">
                                <?php if (!empty($usernameError)) echo $usernameError; ?>
                            </span>
                        </div>

                        <div class="CashierRegister__form-group">
                            <label for="password">
                                Password
                                <span class="required">*</span>
                            </label>
                            <input type="password" id="password" name="password" autocomplete="off" required>
                            <span class="error-message"></span>
                        </div>

                        <div class="CashierRegister__form-group remove-margin">
                            <label for="confirmPassword">
                                Confirm Password
                                <span class="required">*</span>
                            </label>
                            <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="off" required>
                            <span class="error-confirm-message"></span>
                        </div>

                        <div class="CashierRegister__password-requirements">
                            <p>Your password must include the following:</p>
                            <ul>
                                <li id="lengthRequirement"><span class="wrong">&#10005;</span> Be 8–100 characters long</li>
                                <li id="caseRequirement"><span class="wrong">&#10005;</span> Contain at least one uppercase and one lowercase letter</li>
                                <li id="specialRequirement"><span class="wrong">&#10005;</span> Contain at least one number or special character</li>
                            </ul>
                        </div>

                        <button type="submit" class="CashierRegister__register-btn">Register</button>

                        <div class="CashierRegister__login-link">
                            Already have an account? <a href="cashier_login.php">Login here</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <div id="successModal" class="modal">
        <div class="Success__modal-content">
            <div class="Success__modal-header">
                <h1>Registration Successful!</h1>
            </div>
            <div class="Success__modal-body">
                <p>Your account has been created successfully.</p>
                <p>You will be redirected to the login page shortly.</p>
            </div>
            <div class="Success__modal-footer">
                <button id="loginNowBtn">Close</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/cashier/cashier_registerer.js"></script>
    <?php if (isset($registrationSuccess) && $registrationSuccess === true): ?>
        <script>
            document.getElementById('successModal').style.display = 'flex';
            document.getElementById('loginNowBtn').addEventListener('click', function() {
                document.getElementById('successModal').style.display = 'none';
                window.location.href = 'cashier_login.php';
            });
        </script>
    <?php endif; ?>
</body>

</html