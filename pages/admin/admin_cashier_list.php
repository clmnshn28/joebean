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


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Item List | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_item_list.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_cashier_list.css">
        <link rel="stylesheet" href="../../assets/css/modals.css">
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
                        Transaction Record
                    </a>
                </nav>
                <div class="AdminItemList__logout-container">
                    <a href="admin_item_list.php?action=logout" class="AdminItemList__logout">
                        <img src="../../assets/images/logout-icon.svg" alt="logout icon">
                        Log Out
                    </a>
                </div>
            </div>

            <div class="AdminItemList__content">
                <div class="AdminItemList__table">
                    <div class="AdminItemList__header-container">
                        <h3>Cashiers List</h3>
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
                        <tbody>
                            <tr>
                                <td><img class="AdminCashierList__table-data-image" src="../../assets/uploads/default.png" alt=""></td>
                                <td>clmnshn</td>
                                <td>Celmin Shane Quizon</td>
                                <td>Male</td>
                                <td>22</td>
                                <td>
                                    <button class="AdminCashierList__table-data-btn">
                                        view
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="AdminItemList__pagination-container">
                        <button class="AdminItemList__pagination-left"> 
                            <img src="../../assets/images/arrow-pagination-icon.svg" alt="arrow icon">
                        </button>
                        <span class="AdminItemList__pagination-number">1</span>
                        <button class="AdminItemList__pagination-right">
                            <img src="../../assets/images/arrow-pagination-icon.svg" alt="arrow icon">
                        </button>
                    </div>
                </div>

            </div>
        </div>



        <!-- Modal structure -->
        <div class="modal" id="itemModal">
            <form class="modal-content">
                <div class="modal-header-container">
                    <!-- <span class="close" id="closeModal">&times;</span> -->
                    <h3>Add New Item</h3>
                </div>

                <div class="AdminItemList__modal-form-container">
                    <div class="AdminItemList__modal-left">
                        <img id="preview" src="../../assets/images/image-preview.jpg" alt="image item-image" class="item-image-icon">
                        <input type="file" name="item-image" id="item-image" accept="image/*" style="display: none;">
                        <button type="button" class="AdminItemList__modal-select-image-btn" onclick="document.getElementById('item-image').click();">
                            <img src="../../assets/images/upload-icon.svg" alt="upload icon">
                            Upload photo
                        </button>
                        <span class="error-image-message"></span>     
                    </div>

                    <div class="AdminItemList__modal-right">
                        <div class="AdminItemList__modal-form-group">
                            <label for="item-name">
                                ITEM NAME 
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="item-name" name="item-name"  autocomplete="off" required>
                        </div>
                        <div class="AdminItemList__modal-form-group">
                            <label for="item-category">
                                ITEM CATEGORY
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="item-category" name="item-category"  autocomplete="off" required>
                        </div>
                        <div class="AdminItemList__modal-form-group">
                            <label for="item-price">
                                ITEM PRICE
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="item-price" name="item-price"  autocomplete="off" required>
                        </div>
                        <div class="AdminItemList__modal-form-group">
                            <label for="item-stock">
                                ITEM STOCK
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="item-stock" name="item-stock"  autocomplete="off" required>
                        </div>
                        <div class="AdminItemList__modal-form-group">
                            <label for="item-size">
                                ITEM SIZE
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="item-size" name="item-size"  autocomplete="off" required>
                        </div>
                    </div>
                </div>
               
                <div class="modal-footer-container">
                    <button type="button" class="AdminItemList__modal-cancel-button">
                        Cancel
                    </button>
                    <button type="submit" class="AdminItemList__modal-save-button">
                        Add item
                    </button>
                </div>

            </form>
        </div>

        <script src="../../assets/js/admin/admin_item_list.js"></script>
    </body>
</html>