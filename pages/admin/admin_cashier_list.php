    <?php
    // Start the session
    session_start();

    include "../../config/db.php";

    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../pages/auth/admin_login.php");
        exit();
    }

    // Handle logout
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect to login page
        header("Location: ../../pages/auth/admin_login.php");
        exit();
    }

    // Function to calculate age from birthdate
    function calculateAge($birthYear, $birthMonth, $birthDay)
    {
        $birthDate = new DateTime("$birthYear-$birthMonth-$birthDay");
        $today = new DateTime();
        $diff = $today->diff($birthDate);
        return $diff->y;
    }

    // ===================================================================
    // for resetting password
    if (isset($_GET['action']) && $_GET['action'] === 'resetPassword') {

        $userId = $_POST['user_id'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];


        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update in database
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='cashier'");
        $stmt->bind_param("si", $hashedPassword, $userId);

        if ($stmt->execute()) {
            $_SESSION['reset_success'] = "The password has been successfully reset for this cashier account.";
        } else {
            $_SESSION['reset_error'] = "Failed to reset password.";
        }

        $stmt->close();
        header("Location: admin_cashier_list.php");
        exit();
    
    }


    // =====================================================================
    // Handle deactivation

    if (isset($_GET['action']) && $_GET['action'] === 'deactivate') {

        $deactivateUserId = $_GET['deactivate_user_id'];

        $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ? AND role = 'cashier'");
        $stmt->bind_param("i", $deactivateUserId);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: admin_cashier_list.php");
        exit();
    }

    //  echo "<script>console.log('Not a POST request: " . $_SERVER["REQUEST_METHOD"] . "');</script>";
    
    // Handle reactivation
    if (isset($_GET['action']) && $_GET['action'] === 'reactivate') {

        $reactivateUserId = $_GET['reactivate_user_id'];

        $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ? AND role = 'cashier'");
        $stmt->bind_param("i", $reactivateUserId);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_cashier_list.php");
        exit();
    }

    // ================================================================

    if (isset($_POST['export_excel'])) {
        $check_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='cashier'");
        $count_data = mysqli_fetch_assoc($check_result);
        
           
        if ($count_data['count'] == 0) {
            $_SESSION['export_error'] = "Unable to export data to Excel. There are no items available to export.";
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect back to the same page
            exit();
        } else {
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment; filename="cashiers_list_' . date('Y-m-d') . '.xls"');
            date_default_timezone_set('Asia/Manila');

             // Create the Excel content
             echo '
             <html>
             <head>
                 <style>
                     td, th {
                         border: 1px solid #000000;
                         padding: 5px;
                     }
                     .transaction-header {
                         background-color: #f0f0f0;
                     }
                     .transaction-separator {
                         background-color: #cccccc;
                         height: 3px;
                     }
                     .item-row {
                         background-color: #ffffff;
                     }
                     .total-row {
                         background-color: #e6e6e6;
                         font-weight: bold;
                     }
                 </style>
             </head>
             <body>
                 <table>
                     <tr>
                         <th colspan="7" style="font-size: 16pt; text-align: center; background-color: #656D4A; color: white;">JoeBean Cashiers List</th>
                     </tr>
                     <tr>
                         <th colspan="7" style="font-size: 11pt; text-align: center;">Generated on: ' . date('Y-m-d - h:i A') . '</th>
                     </tr>
                     <tr>
                         <th style="background-color: #656D4A; color: white;">Cashier ID</th>
                         <th style="background-color: #656D4A; color: white;">Username</th>
                         <th style="background-color: #656D4A; color: white;">Full Name</th>
                         <th style="background-color: #656D4A; color: white;">Gender</th>
                         <th style="background-color: #656D4A; color: white;">Age</th>
                         <th style="background-color: #656D4A; color: white;">Birthdate</th>
                         <th style="background-color: #656D4A; color: white;">Account Created</th>
                     </tr>';
            
            $export_result = mysqli_query($conn, "SELECT * FROM users WHERE role='cashier' ORDER BY id ASC");
        
            while ($row = mysqli_fetch_assoc($export_result)) {
                $fullname = ucwords(strtolower($row['firstname'])) . ' ' .
                    (isset($row['middlename']) ? ucwords(strtolower($row['middlename'])) . ' ' : '') .
                    ucwords(strtolower($row['lastname']));
                
                $age = calculateAge($row['birth_year'], $row['birth_month'], $row['birth_day']);
                $birthdate = date("F d, Y", strtotime("{$row['birth_year']}-{$row['birth_month']}-{$row['birth_day']}"));
                $created_at = date("Y-m-d - h:i A", strtotime($row['created_at']));
                
                // Determine status
                $status = ($row['deleted_at'] === NULL) ? "Active" : "Deactivated";
                echo "<tr>";
                    echo "<td style='text-align: center; vertical-align: middle; font-weight: bold;'>" . $row['id'] . "</td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $fullname . "</td>";
                    echo "<td>" . ucwords(strtolower($row['gender'])) . "</td>";
                    echo "<td>" . $age . "</td>";
                    echo "<td>" . $birthdate . "</td>";
                    echo "<td>" . $created_at . "</td>";
                echo "</tr>";
            }
       
            echo '
                </table>
            </body>
            </html>';
            exit; 
        }

    }

    // Set number of records per page
    $limit = 7;
    // Get the current page number from the URL, default is 1
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

    // Get the total number of cashier users (including deactivated ones)
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users 
                            WHERE role='cashier'
                            AND (username LIKE '%$search_term%' OR firstname LIKE '%$search_term%' OR lastname LIKE '%$search_term%' OR middlename LIKE '%$search_term%')");
    $total_users = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_users / $limit);

    // Fetch only the current page's data
    $result = mysqli_query($conn, "SELECT * FROM users 
            WHERE role='cashier'
            AND (username LIKE '%$search_term%' OR firstname LIKE '%$search_term%' OR lastname LIKE '%$search_term%' OR middlename LIKE '%$search_term%') 
            ORDER BY id DESC 
            LIMIT $limit OFFSET $offset");

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Item List | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_item_list.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_cashier_lists.css">
        <link rel="stylesheet" href="../../assets/css/modall.css">
    </head>

    <body>
        <div class="AdminItemList__main-content">
            <div class="AdminItemList__sidebar">
                <div class="AdminItemList__branding">
                    <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="AdminItemList__logo" />
                    <div class="AdminItemList__system-name">
                        <p class="AdminItemList__title">JoeBean</p>
                        <p class="AdminItemList__subtitle">Point-of-Sale System with Inventory</p>
                    </div>
                </div>
                <nav class="AdminItemList__nav">
                    <a href="admin_item_list.php">
                        <img src="../../assets/images/item-list-icon.svg" alt="item-list-icon">
                        Items
                    </a>
                    <a href="admin_cashier_list.php" class="active">
                        <img src="../../assets/images/person-icon.svg" alt="person-icon">
                        Cashiers
                    </a>
                    <a href="admin_transaction_record.php">
                        <img src="../../assets/images/time-icon.svg" alt="time-icon">
                        Transaction Records
                    </a>
                    <a href="admin_daily_sales.php">
                        <img src="../../assets/images/chart-sales-icon.svg" alt="daily-sales-icon">
                        Daily Sales
                    </a>
                </nav>
                <div class="AdminItemList__logout-container">
                    <button class="AdminItemList__logout">
                        <img src="../../assets/images/logout-icon.svg" alt="logout icon">
                        Log Out
                    </button>
                </div>
            </div>

            <div class="AdminItemList__content">
                <div class="AdminItemList__table">
                    <div class="AdminItemList__header-container">
                        <h3>Cashiers List</h3>
                        <div class="AdminItemList__header-search-container">
                            <div class="AdminItemList__search-content">
                                <input type="text" autocomplete="off" placeholder="Search">
                                <span></span>
                                <img src="../../assets/images/search-icon.svg" alt="search icon">
                            </div>
                            <form method="post">
                                <button class="AdminItemList__excel-btn" type="submit" name="export_excel">
                                    <img src="../../assets/images/excel-icon.svg" alt="">
                                </button>
                            </form>
                        </div>
                    </div>
                    <table class="AdminCashierList__table-content-item">
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Username</th>
                                <th>Fullname</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cashierTableBody">
                            <!-- <tr>
                                <td><img class="AdminCashierList__table-data-image" src="../../assets/uploads/default.png" alt=""></td>
                                <td>clmnshn</td>
                                <td>Celmin Shane Quizon</td>
                                <td>Male</td>
                                <td>22</td>
                                <td>
                                  <button class="AdminCashierList__table-data-btn" id="viewButton">
                                        view
                                    </button>  
                                    <button class="AdminCashierList__table-reactivate-data-btn" id="reactivateButton">
                                        Reactivate
                                    </button>
                                </td>
                            </tr> -->

                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $fullname = ucwords(strtolower($row['firstname'])) . ' ' .
                                        (isset($row['middlename']) ? ucwords(strtolower($row['middlename'])) . ' ' : '') .
                                        ucwords(strtolower($row['lastname']));
                                    $age = calculateAge($row['birth_year'], $row['birth_month'], $row['birth_day']);
                                    $image_path = !empty($row['image']) ? $row['image'] : 'uploads/default.png';
                                    $created_at_formatted = date("F d, Y — h : i A", strtotime($row['created_at']));
                                    $birthdate = date("F d, Y", strtotime("{$row['birth_year']}-{$row['birth_month']}-{$row['birth_day']}"));

                                    echo "<tr>";
                                    echo "<td><img class='AdminCashierList__table-data-image' src='../../assets/images/avatars/" . htmlspecialchars($image_path) . "' alt='Profile Image'></td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($fullname) . "</td>";
                                    echo "<td>" . htmlspecialchars(ucwords(strtolower($row['gender']))) . "</td>";
                                    echo "<td>" . $age . "</td>";
                                    echo "<td>";

                                    if ($row['deleted_at'] === NULL) {
                                        echo "<button 
                                        class='AdminCashierList__table-data-btn' 
                                        data-id='" . htmlspecialchars($row['id']) . "' 
                                        data-username='" . htmlspecialchars($row['username']) . "' 
                                        data-fullname='" . htmlspecialchars($fullname) . "' 
                                        data-gender='" . htmlspecialchars(ucwords(strtolower($row['gender']))) . "' 
                                        data-age='" . htmlspecialchars($age) . "' 
                                        data-birthdate='" . htmlspecialchars($birthdate) . "' 
                                        data-created-at='" . $created_at_formatted . "' 
                                        data-image='" . htmlspecialchars($image_path) . "'>
                                        View
                                    </button>";
                                    } else {
                                        echo "<button 
                                        class='AdminCashierList__table-reactivate-data-btn' 
                                        data-id='" . htmlspecialchars($row['id']) . "'
                                        >
                                            Reactivate
                                        </button>";
                                    }

                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo '<tr class="no-data-row">
                                        <td colspan="6">
                                            <div class="no-data-message">
                                                No Cashier List
                                            </div>
                                        </td>
                                    </tr>';
                            }

                            ?>
                        </tbody>
                    </table>
                    <div class="AdminItemList__pagination-container" id="paginationContainer">
                        <button class="AdminItemList__pagination-left" <?php if ($page <= 1) echo 'disabled'; ?>>
                            <img src="../../assets/images/arrow-pagination-icon.svg" alt="arrow icon">
                        </button>
                        <span class="AdminItemList__pagination-number"><?php echo $page; ?> of <?php echo $total_pages; ?></span>
                        <button class="AdminItemList__pagination-right" <?php if ($page >= $total_pages) echo 'disabled'; ?>>
                            <img src="../../assets/images/arrow-pagination-icon.svg" alt="arrow icon">
                        </button>
                    </div>
                </div>

            </div>
        </div>



        <!-- Modal structure -->
        <div class="modal" id="itemModal">
            <div class="modal-content AdminCashierList__relative-modal">
                <div class="modal-header-container AdminCashierList__modal-header">
                    <!-- <span class="close" id="closeModal">&times;</span> -->
                    <h3>Cashier Details</h3>
                    <button type="button" class="AdminCashierList__modal-reset-password-button">
                        Reset Password
                    </button>
                </div>

                <div class="AdminCashierList__modal-form-container">
                    <div class="AdminCashierList__modal-left">
                        <img id="modalImage" src="../../assets/images/image-preview.jpg" alt="image item-image" class="AdminCashierList__item-image-icon">
                    </div>

                    <div class="AdminCashierList__modal-right">
                        <p class="AdminCashierList__fullname" id="modalFullname"></p>

                        <div class="AdminCashierList__details-list">
                            <p class="AdminCashierList__detail-name">
                                Username <span>:</span>
                            </p>
                            <p class="AdminCashierList__detail-value" id="modalUsername"></p>
                        </div>
                        <div class="AdminCashierList__details-list">
                            <p class="AdminCashierList__detail-name">
                                Gender <span>:</span>
                            </p>
                            <p class="AdminCashierList__detail-value" id="modalGender"></p>
                        </div>
                        <div class="AdminCashierList__details-list">
                            <p class="AdminCashierList__detail-name">
                                Age <span>:</span>
                            </p>
                            <p class="AdminCashierList__detail-value" id="modalAge"></p>
                        </div>
                        <div class="AdminCashierList__details-list">
                            <p class="AdminCashierList__detail-name">
                                Birthdate <span>:</span>
                            </p>
                            <p class="AdminCashierList__detail-value" id="modalBirthdate"></p>
                        </div>
                        <div class="AdminCashierList__details-list">
                            <p class="AdminCashierList__detail-name">
                                Account Created <span>:</span>
                            </p>
                            <p class="AdminCashierList__detail-value" id="modalCreatedAt"></p>
                        </div>
                    </div>
                </div>

                <div class="AdminCashierList__modal-footer-container">
                    <button type="button" class="AdminCashierList__modal-cancel-button" id="closeModal">
                        Close
                    </button>
                </div>
            </div>
        </div>


        <!-- Modal structure -->
        <div class="modal" id="resetPasswordModal">
            <div class="Modal_fade-in AdminCashierList__modal-reset-pass-content">
                <div class="AdminCashierList__modal-reset-pass-header-container">
                    <h3>Reset Password</h3>
                </div>
                <form action="" method="post" class="AdminCashierList__modal-reset-pass-form-container">
                    <input type="hidden" name="user_id" id="resetUserId">
                    <input type="hidden" name="action" value="resetPassword">
                    <div class="AdminCashierList__modal-reset-pass-details-list">
                        <p class="AdminCashierList__modal-reset-pass-detail-name">
                            Username <span>:</span>
                        </p>
                        <p class="AdminCashierList__modal-reset-pass-detail-value">
                            <span id="resetUsername"></span>
                        </p>
                    </div>

                    <div class="AdminCashierList__modal-reset-pass-form-group">
                        <label for="password">
                            Password :
                            <span class="required">*</span>
                        </label>
                        <input type="password" id="password" name="password" autocomplete="off" required>
                    </div>

                    <div class="AdminCashierList__modal-reset-pass-form-group remove-margin">
                        <label for="confirmPassword">
                            Confirm Password :
                            <span class="required">*</span>
                        </label>
                        <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="off" required>
                    </div>

                    <p class="AdminCashierList__modal-reset-pass-error-message">
                        <img src="../../assets/images/error-icon.svg" alt="error icon">
                        Incorrect Password
                    </p>

                    <div class="AdminCashierList__modal-reset-pass-password-requirements">
                        <p>Your password must include the following:</p>
                        <ul>
                            <li id="lengthRequirement"><span class="wrong">&#10005;</span> Be 8–100 characters long</li>
                            <li id="caseRequirement"><span class="wrong">&#10005;</span> Contain at least one uppercase and one lowercase letter</li>
                            <li id="specialRequirement"><span class="wrong">&#10005;</span> Contain at least one number or special character</li>
                        </ul>
                    </div>

                    <div class="AdminCashierList__modal-reset-pass-button-group">
                        <button type="submit" class="AdminCashierList__modal-full-reset-pass-button" id="fullResetPassword">
                            Reset Password
                        </button>
                        <div class="AdminCashierList__modal-reset-pass-button-inner-group">
                            <button type="button" class="AdminCashierList__modal-cancel-pass-button">
                                Cancel
                            </button>
                            <button type="button" class="AdminCashierList__modal-deact-pass-button">
                                Deactivate
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>

        <!-- Modal structure -->
        <div class="modal" id="deactivateModal">
            <div class="Modal_fade-in AdminCashierList__modal-deactivate-content">
                <div class="AdminCashierList__modal-reset-pass-header-container">
                    <h3>Deactivate Cashier Account</h3>
                </div>

                <p class="AdminCashierList__modal-deactivate-first-p">Are you sure you want to deactivate this account?</p>
                <p class="AdminCashierList__modal-deactivate-second-p">This account will prevent the worker from accessing the system.</p>
                <!-- <p class="AdminCashierList__modal-deactivate-username">Username: <span>clmnshn</span></p> -->
                <form action="" method="get" id="deactivateForm">
                    <input type="hidden" name="deactivate_user_id" id="deactivateUserId">
                    <input type="hidden" name="action" value="deactivate">

                    <div class="AdminCashierList__modal-deactivate-button-inner-group">
                        <button type="button" class="AdminCashierList__modal-deactivate-cancel-pass-button">
                            Cancel
                        </button>
                        <button type="submit" class="AdminCashierList__modal-deactivate-confirm-pass-button">
                            Deactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal structure -->
        <div class="modal" id="reactivateModal">
            <div class="Modal_fade-in AdminCashierList__modal-deactivate-content">
                <div class="AdminCashierList__modal-reset-pass-header-container">
                    <h3>Reactivate Cashier Account</h3>
                </div>

                <p class="AdminCashierList__modal-deactivate-first-p">Are you sure you want to reactivate this account?</p>
                <p class="AdminCashierList__modal-deactivate-second-p"> Reactivating the account will allow the worker to access the system again.</p>
                <!-- <p class="AdminCashierList__modal-deactivate-username">Username: <span>clmnshn</span></p> -->
                <form action="" method="get" id="reactivateForm">
                    <input type="hidden" name="reactivate_user_id" id="reactivateUserId">
                    <input type="hidden" name="action" value="reactivate">

                    <div class="AdminCashierList__modal-deactivate-button-inner-group">
                        <button type="button" class="AdminCashierList__modal-reactivate-cancel-pass-button">
                            Cancel
                        </button>
                        <button type="submit" class="AdminCashierList__modal-reactivate-confirm-pass-button">
                            Reactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="logoutSideBarModal" >
            <div class="Modal_fade-in Logout__modal-content">
                <div class="Logout__modal-content-header-container">
                    <img src="../../assets/images/logout-confirm-icon.svg" alt="Logout icon">
                    <h3>Logout</h3>
                </div>
                <p class="Logout__modal-p">Are you sure you want to logout your account?</p>
                <div class="Logout__modal-button-group">
                    <button type="button" class="Logout__modal-cancel-button">
                        Cancel
                    </button>
                    <a href="admin_item_list.php?action=logout" class="Logout__modal-confirm-button">
                        Confirm
                    </a>
                </div>
            </div>
        </div>

        <div class="modal" id="successResetPasswordModal">
            <div class="Modal_fade-in ErrorPayment__modal-content">
                <div class="ErrorPayment__modal-content-header-container">
                    <img class="error-icon" src="../../assets/images/error-icon.svg" alt="Logout icon">
                    <img class="success-icon" src="../../assets/images/successful-icon.svg" alt="SuccessFull icon">
                    <h3></h3>
                </div>
                <p class="ErrorPayment__modal-p"></p>
                <div class="ErrorPayment__modal-button-group">
                    <button type="button" class="ErrorPayment__modal-cancel-button">
                        Close
                    </button>
                </div>
            </div>
        </div>


        <script src="../../assets/js/admin/admin_cashier_list.js"></script>

        <script>
            const successModal = document.getElementById('successResetPasswordModal');
            const icons = document.querySelectorAll('.ErrorPayment__modal-content-header-container img');
            const errorIcon = icons[0];   
            const successIcon = icons[1];
            const modalTitle = successModal.querySelector('h3');
            const modalMessage = successModal.querySelector('.ErrorPayment__modal-p');


            <?php if(isset($_SESSION['reset_success'])): ?>

                successModal.style.display = 'flex';

                modalTitle.style.color = "#4CAF50";
                modalTitle.textContent = 'Password Reset Successful';

                modalMessage.textContent = '<?php echo $_SESSION['reset_success']; ?>';
            
                errorIcon.style.display = 'none';
                successIcon.style.display = 'block';

                successModal.querySelector('.ErrorPayment__modal-cancel-button').addEventListener('click', function() {
                    successModal.style.display = 'none';
                });
                
                <?php unset($_SESSION['reset_success']); ?>
                
            <?php endif; ?>
                
            // Similarly for error messages
            <?php if(isset($_SESSION['reset_error'])): ?>

                successModal.style.display = 'flex';
                
                modalTitle.style.color = "#a53f3f";
                modalTitle.textContent = 'Password Reset Unsuccessful';
                
                modalMessage.textContent = '<?php echo $_SESSION['reset_error']; ?>';
                                
                errorIcon.style.display = 'block';
                successIcon.style.display = 'none';
                
                successModal.querySelector('.ErrorPayment__modal-cancel-button').addEventListener('click', function() {
                    successModal.style.display = 'none';
                });
                    
                <?php unset($_SESSION['reset_error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['export_error'])): ?>

                successModal.style.display = 'flex';

                modalTitle.style.color = "#a53f3f";
                modalTitle.textContent = 'Export Failed';

                modalMessage.textContent = '<?php echo $_SESSION['export_error']; ?>';
                                
                errorIcon.style.display = 'block';
                successIcon.style.display = 'none';

                successModal.querySelector('.ErrorPayment__modal-cancel-button').addEventListener('click', function() {
                    successModal.style.display = 'none';
                });
                    
                <?php unset($_SESSION['export_error']); ?>
            <?php endif; ?>
        </script>
    </body>

    </html>