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

    if (isset($_POST['export_excel'])) {
        $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM transactions");
        $count_data = mysqli_fetch_assoc($count_query);

        if ($count_data['count'] == 0) {
            $_SESSION['export_error'] = "Unable to export data to Excel. There are no items available to export.";
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect back to the same page
            exit();
        } else {
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment; filename="transaction_records_' . date('Y-m-d') . '.xls"');
            
            $export_result = mysqli_query($conn, "
                SELECT 
                    t.id AS transaction_id,
                    t.ref_no,
                    t.payment_method, 
                    t.created_at, 
                    t.quantity, 
                    t.unit_price, 
                    t.total_amount, 
                    CONCAT(u.firstname, ' ', u.lastname) AS cashier_name, 
                    t.product_item, 
                    p.item_category
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                JOIN products p ON t.product_id = p.id
                ORDER BY t.created_at ASC
            ");
        
            echo "<table border='1'>";
            echo "<thead>";
            echo "<tr>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Transaction ID</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Cashier</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Product</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Category</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Qty</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Price</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Total</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Payment</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Reference No.</th>
                    <th style='background-color: #656D4A; color: white; font-size: 21px;'>Date</th>
                </tr>";
            echo "</thead>";
            echo "<tbody>";
     
            while ($row = mysqli_fetch_assoc($export_result)) {
                echo "<tr style='font-size: 20px;'>";
                    echo "<td>" . $row['transaction_id'] . "</td>";
                    echo "<td>" . $row['cashier_name'] . "</td>";
                    echo "<td>" . $row['product_item'] . "</td>";
                    echo "<td>" . $row['item_category'] . "</td>";
                    echo "<td>" . $row['quantity'] . "</td>";
                    echo "<td>&#8369;" . $row['unit_price'] . "</td>";
                    echo "<td>&#8369;" . $row['total_amount'] . "</td>";
                    echo "<td>" . $row['payment_method'] . "</td>";
                    echo "<td style='text-align: center;'>" . ($row['ref_no'] ? $row['ref_no'] : '-')  . "</td>";
                    echo "<td>" . date("F d, Y - h:i A", strtotime($row['created_at'])) . "</td>";
                echo "</tr>";
            }
        
            echo "</tbody>";
            echo "</table>";
            exit(); 
        }
    }
    

    $limit = 6;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    // Get the total number of transactions
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions");
    $total_transactions = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_transactions / $limit);
    
    // Fetch paginated transaction records with user and product info
    $result = mysqli_query($conn, "
        SELECT 
            t.id AS transaction_id, 
            t.ref_no,
            t.payment_method, 
            t.created_at, 
            t.quantity, 
            t.unit_price, 
            t.total_amount, 
            CONCAT(u.firstname, ' ', u.lastname) AS cashier_name, 
            t.product_item, 
            p.item_category, 
            p.item_image, 
            u.image AS cashier_image
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN products p ON t.product_id = p.id
        ORDER BY t.created_at DESC
        LIMIT $limit OFFSET $offset
    ");



?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Item List | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_item_lists.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_transaction_records.css">
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
                    <a href="admin_cashier_list.php">
                        <img src="../../assets/images/person-icon.svg" alt="person-icon">
                         Cashiers
                    </a>
                    <a href="admin_transaction_record.php" class="active">
                        <img src="../../assets/images/time-icon.svg" alt="time-icon">
                        Transaction Records
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
                        <h3>Transaction Records</h3>
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
                    <table class="AdminTransactionRecords__table-content-item">
                        <thead>
                            <tr>
                                <th>Cashier</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total Price</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody">
                            <!-- <tr>
                                <td>
                                    <div class='item-with-image'>
                                        <img class='AdminTransactionRecords__item-image' src="../../assets/images/avatars/default.jpg" alt="">
                                        Celmin Shane Quizon
                                    </div>
                                </td>
                                <td>Nachos</td>
                                <td>1</td>
                                <td>89</td>
                                <td>89</td>
                                <td>Cash</td>
                                <td>03/10/2025</td>
                                <td>
                                  <button class="AdminCashierList__table-data-btn" id="viewButton">
                                        view
                                    </button>  
                                </td>
                            </tr> -->
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $created_at_formatted = date("m/d/Y", strtotime($row['created_at']));
                                    $created_at_modal_formatted = date("M d, Y — h:i A", strtotime($row['created_at']));
                                    echo "<tr>";
                                        echo "<td>
                                            <div class='item-with-image'>
                                                <img class='AdminTransactionRecords__item-image' src='../../assets/images/avatars/" . htmlspecialchars($row['cashier_image']) . "' alt='Item Image'>
                                                <span>" . htmlspecialchars(ucwords(strtolower($row['cashier_name']))) . "</span>
                                            </div>
                                        </td>";
                                        echo "<td>" . htmlspecialchars($row['product_item']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                        echo "<td>₱" . htmlspecialchars($row['unit_price']) . "</td>";
                                        echo "<td>₱" . htmlspecialchars($row['total_amount']) . "</td>";
                                        // echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                                        echo "<td>
                                                <div class='AdminTransactionRecord__item-with-references'>
                                                    <p>" . htmlspecialchars(ucwords(strtolower($row['payment_method']))) . "</p>";         
                                            // Check if payment method is GCash
                                            if (strtolower($row['payment_method']) === 'gcash') {
                                                echo "<p class='AdminTransactionRecord__item-reference-number' id='modalReferenceNumber'>" . htmlspecialchars(ucwords(strtolower($row['ref_no']))) . "</p>";
                                            }
                                        echo "</div>
                                            </td>";
                                        echo "<td>" . $created_at_formatted. "</td>";
                                        echo "<td>";
                                            echo "<button 
                                                class='AdminTransactionRecord__table-data-btn'
                                                data-productImage='" . htmlspecialchars($row['item_image']) . "'
                                                data-transactionId='" . htmlspecialchars($row['transaction_id']) . "'
                                                data-createdAt='" . $created_at_modal_formatted . "'
                                                data-cashierImage='" . htmlspecialchars($row['cashier_image']) . "'
                                                data-CashierName='" . htmlspecialchars(ucwords(strtolower($row['cashier_name']))) . "'
                                                data-itemName='" . htmlspecialchars($row['product_item']) . "'
                                                data-itemCategory='" . htmlspecialchars($row['item_category']) . "'
                                                data-itemPrice='₱" . htmlspecialchars($row['unit_price']) . "'
                                                data-itemQuantity='" . htmlspecialchars($row['quantity']) . "'
                                                data-itemAmount='₱" . htmlspecialchars($row['total_amount']) . "'
                                                data-paymentMethod='" . htmlspecialchars($row['payment_method']) . "'
                                                data-referenceNumber='" . htmlspecialchars($row['ref_no']) . "'
                                                >
                                                    View
                                                </button>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo '<tr class="no-data-row"><td colspan="8"><div class="no-data-message">No Transaction Records</div></td></tr>';
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
        <div class="modal" id="itemModal" >
            <div class="modal-content AdminTransactionRecord__relative-modal">
                <div class="modal-header-container AdminTransactionRecord__modal-header">
                    <h3>Transaction Record Details</h3>
        
                </div>

                <div class="AdminTransactionRecord__modal-form-container">
                    <div class="AdminTransactionRecord__modal-left">
                        <img id="modalProductImage" src="../../assets/images/products/1744201221_dark-choco.png" alt="image item-image" class="AdminTransactionRecord__item-image-icon">
                    
                        <div class="AdminTransactionRecord__id-time-container">
                            <div class="AdminTransactionRecord__details-id-time-list">
                                <p class="AdminTransactionRecord__detail-id-time-name">
                                    Transaction ID<span>:</span>
                                </p>
                                <p class="AdminTransactionRecord__detail-id-time-value" id="modalTransactionId">2</p>
                            </div>
                            <p class="AdminTransactionRecord__created-at" id="modalCreatedAt">Jan 20, 2002 | 10:50 PM</p>
                        </div>
                    </div>

                    <div class="AdminTransactionRecord__modal-right">
                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Cashier Name<span>:</span>
                            </p>
                            <div class='AdminTransactionRecord__item-with-image'>
                                <img id="modalCashierImage" class='AdminTransactionRecords__modal-item-image' src='../../assets/images/avatars/1744201927_Picture.jpg' alt='Item Image'>
                                <span id="modalCashierName">Celmin Shane Quizon</span>
                            </div>
                        </div>

                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Item Name<span>:</span>
                            </p>
                            <p class="AdminTransactionRecord__detail-value" id="modalItemName">Dark Choco Milk</p>
                        </div>

                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Item Category<span>:</span>
                            </p>
                            <p class="AdminTransactionRecord__detail-value" id="modalItemCategory">Iced Non-Coffee</p>
                        </div>
                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Item Price<span>:</span>
                            </p>
                            <p class="AdminTransactionRecord__detail-value" id="modalItemPrice">₱89.00 / Venti</p>
                        </div>
                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Quantity<span>:</span>
                            </p>
                            <p class="AdminTransactionRecord__detail-value" id="modalItemQuantity">2</p>
                        </div>
                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Total Amount<span>:</span>
                            </p>
                            <p class="AdminTransactionRecord__detail-value" id="modalItemAmount">₱173.00</p>
                        </div>
                        <div class="AdminTransactionRecord__details-list">
                            <p class="AdminTransactionRecord__detail-name">
                                Payment Method<span>:</span>
                            </p>
                            <p class="AdminTransactionRecord__detail-value" id="modalPaymentMethod">GCash <span class="AdminTransactionRecord__reference-number" id="modalReferenceNumber">REF20250410A</span></p>
                        </div>
                    </div>
                </div>

                <div class="AdminTransactionRecord__modal-footer-container">
                    <button type="button" class="AdminTransactionRecord__modal-cancel-button" id="closeModal">
                        Close
                    </button>
                </div>
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

        <div class="modal" id="ErrorExportModal">
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

        <script src="../../assets/js/admin/admin_transaction_recorder.js"></script>
        <script>
            const successModal = document.getElementById('ErrorExportModal');
            const icons = document.querySelectorAll('.ErrorPayment__modal-content-header-container img');
            const errorIcon = icons[0];   
            const successIcon = icons[1];
            const modalTitle = successModal.querySelector('h3');
            const modalMessage = successModal.querySelector('.ErrorPayment__modal-p');
                
            // Similarly for error messages
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