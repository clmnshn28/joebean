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

    if(isset($_POST['action']) && $_POST['action'] == 'get_transaction_details') {
        $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);
        
        // Get transaction header info
        $transaction_query = mysqli_query($conn, "
            SELECT 
                t.id AS transaction_id,
                t.ref_no,
                t.payment_method,
                t.total_amount,
                t.created_at,
                CONCAT(u.firstname, ' ', u.lastname) AS cashier_name,
                u.image AS cashier_image
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = '$transaction_id'
        ");
        
        $transaction = mysqli_fetch_assoc($transaction_query);
        
        // Get transaction items
        $items_query = mysqli_query($conn, "
            SELECT 
                ti.quantity,
                ti.unit_price,
                ti.total_price AS item_total,
                p.item_name,
                p.item_category,
                p.item_image,
                pv.item_size
            FROM transaction_items ti
            JOIN products p ON ti.product_id = p.id
            JOIN product_variants pv ON ti.product_variant_id = pv.id
            WHERE ti.transaction_id = '$transaction_id'
        ");
        
        $items = [];
        while($item = mysqli_fetch_assoc($items_query)) {
            $items[] = $item;
        }
        
        $result = [
            'transaction' => $transaction,
            'items' => $items
        ];
        
        echo json_encode($result);
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
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="transaction_records_' . date('Y-m-d') . '.xls"');
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
                        <th colspan="11" style="font-size: 16pt; text-align: center; background-color: #656D4A; color: white;">JoeBean Transaction Records</th>
                    </tr>
                    <tr>
                        <th colspan="11" style="font-size: 11pt; text-align: center;">Generated on: ' . date('Y-m-d - h:i A') . '</th>
                    </tr>
                    <tr>
                        <th style="background-color: #656D4A; color: white;">Transaction ID</th>
                        <th style="background-color: #656D4A; color: white;">Cashier</th>
                        <th style="background-color: #656D4A; color: white;">Payment Method</th>
                        <th style="background-color: #656D4A; color: white;">Reference No.</th>
                        <th style="background-color: #656D4A; color: white;">Item</th>
                        <th style="background-color: #656D4A; color: white;">Category</th>
                        <th style="background-color: #656D4A; color: white;">Size</th>
                        <th style="background-color: #656D4A; color: white;">Quantity</th>
                        <th style="background-color: #656D4A; color: white;">Unit Price</th>
                        <th style="background-color: #656D4A; color: white;">Item Total</th>
                        <th style="background-color: #656D4A; color: white;">Date</th>
                    </tr>';
        
            $all_transactions = mysqli_query($conn, "
                SELECT 
                    t.id AS transaction_id, 
                    t.ref_no,
                    t.payment_method, 
                    t.total_amount, 
                    t.created_at,
                    CONCAT(u.firstname, ' ', u.lastname) AS cashier_name
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                ORDER BY t.created_at ASC
            ");
            
            // Loop through each transaction
            while ($row = mysqli_fetch_assoc($all_transactions)) {
                $transaction_id = $row['transaction_id'];
                $created_at_formatted = date("Y-m-d H:i:s", strtotime($row['created_at']));
                $cashier_name = htmlspecialchars(ucwords(strtolower($row['cashier_name'])));
                $payment_info = htmlspecialchars($row['payment_method']);
                $reference_no = htmlspecialchars($row['ref_no']);
                $ref_display = $reference_no ? $reference_no : '-';
                // Get transaction items
                $items_query = mysqli_query($conn, "
                    SELECT 
                        ti.quantity,
                        ti.unit_price,
                        ti.total_price AS item_total,
                        p.item_name,
                        p.item_category,
                        pv.item_size
                    FROM transaction_items ti
                    JOIN products p ON ti.product_id = p.id
                    JOIN product_variants pv ON ti.product_variant_id = pv.id
                    WHERE ti.transaction_id = '$transaction_id'
                ");
                
                $item_count = mysqli_num_rows($items_query);
                if ($item_count > 0) {
              
                    // $item_number = 1;
                    
                    // Transaction summary row
                    echo '<tr class="transaction-header">'; 
                    echo '<td rowspan="' . ($item_count + 2) . '" style="text-align: center; vertical-align: middle; font-weight: bold;">' . $transaction_id . '</td>';
                    echo '<td rowspan="' . ($item_count + 2) . '" style=" vertical-align: middle;">' . $cashier_name . '</td>';
                    echo '<td rowspan="' . ($item_count + 2) . '" style="text-align: center; vertical-align: middle;">' .  $payment_info . '</td>';
                    echo '<td rowspan="' . ($item_count + 2) . '" style="text-align: center; vertical-align: middle;">' . $ref_display . '</td>';
                    echo '<td colspan="6" style="background-color:rgba(194, 197, 170, 0.63);"><strong>Transaction Items:</strong></td>';
                    echo '<td rowspan="' . ($item_count + 2) . '" style="text-align: center; vertical-align: middle;">' .  date("Y-m-d - h:i A", strtotime($row['created_at'])) . '</td>';
                    echo '</tr>';
                    
                    // Item rows
                    while ($item = mysqli_fetch_assoc($items_query)) {
                        $itemSize = htmlspecialchars($item['item_size']);
                   

                        echo '<tr class="item-row">';
                        echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['item_category']) . '</td>';
                        if($itemSize){
                            echo '<td>' . $itemSize . '</td>';
                        }else{
                            echo '<td style="text-align: center; vertical-align: middle;"> - </td>';
                        } 
                        echo '<td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($item['quantity']) . '</td>';
                        echo '<td>&#8369;' . htmlspecialchars($item['unit_price']) . '</td>';
                        echo '<td>&#8369;' . htmlspecialchars($item['item_total']) . '</td>';
                        echo '</tr>';
                        
                        // $item_number++;
                    }
                    
                    // Transaction total row
                    echo '<tr class="total-row">';
                        echo '<td colspan="5" style="text-align: right;">Transaction Total:</td>';
                        echo '<td>&#8369;' . htmlspecialchars($row['total_amount']) . '</td>';
                    echo '</tr>';
                    
                    // Separator row
                    echo '<tr><td colspan="11" class="transaction-separator"></td></tr>';
                }
            }
            
            echo '
                </table>
            </body>
            </html>';
            exit();
        }
    }


    $limit = 6;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    $search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

    // Get the total number of transactions
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as total 
                    FROM transactions t
                    JOIN users u ON t.user_id = u.id
                    WHERE (CONCAT(u.firstname, ' ', u.lastname) LIKE '%$search_term%' OR t.payment_method LIKE '%$search_term%')");
                    
    $total_transactions = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_transactions / $limit);
    
    // Fetch paginated transaction records with user and product info
    $result = mysqli_query($conn, "
        SELECT 
            t.id AS transaction_id, 
            t.ref_no,
            t.payment_method, 
            t.total_amount, 
            t.created_at,
            CONCAT(u.firstname, ' ', u.lastname) AS cashier_name, 
            u.image AS cashier_image
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE (CONCAT(u.firstname, ' ', u.lastname) LIKE '%$search_term%' OR t.payment_method LIKE '%$search_term%')
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
        <link rel="stylesheet" href="../../assets/css/admin/admin_item_list.css">
        <link rel="stylesheet" href="../../assets/css/admin/admin_transaction_record.css">
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
                                    echo "<tr data-transaction-id='" . $row['transaction_id'] . "'>";
                                        echo "<td>
                                            <div class='item-with-image'>
                                                <img class='AdminTransactionRecords__item-image' src='../../assets/images/avatars/" . htmlspecialchars($row['cashier_image']) . "' alt='Item Image'>
                                                <span>" . htmlspecialchars(ucwords(strtolower($row['cashier_name']))) . "</span>
                                            </div>
                                        </td>";
                                        echo "<td>₱" . htmlspecialchars($row['total_amount']) . "</td>";
                                        // echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                                        echo "<td>
                                                <div class='AdminTransactionRecord__item-with-references'>
                                                    <p>" . 
                                                    htmlspecialchars(
                                                        strtolower($row['payment_method']) === 'gcash'
                                                            ? strtoupper(substr(strtolower($row['payment_method']), 0, 2)) . substr(strtolower($row['payment_method']), 2)
                                                            : ucfirst(strtolower($row['payment_method']))
                                                    ). "</p>";         
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
        <div class="modal" id="itemModal">
            <div class="AdminTransactionRecord__relative-modal">
                <div class="AdminTransactionRecord__modal-header">
                    <h3>Transaction Record Details</h3>
                </div>
                <div class="AdminTransactionRecord__modal-form-container">
                    <div class="AdminTransactionRecord__modal-top">
                        
                        <div class="AdminTransactionRecord__top-details-container">
                            <div class="AdminTransactionRecord__details-list">
                                <p class="AdminTransactionRecord__detail-name1">
                                    Cashier Name
                                    <span class="AdminTransactionRecord__detail-name-dash">:</span>
                                </p>
                                <div class='AdminTransactionRecord__item-with-image'>
                                    <img id="modalCashierImage" class='AdminTransactionRecords__modal-item-image' src='../../assets/images/avatars/1744201927_Picture.jpg' alt='Item Image'>
                                    <span id="modalCashierName">Celmin Shane Quizon</span>
                                </div>
                            </div>
                            <div class="AdminTransactionRecord__details-list">
                                <p class="AdminTransactionRecord__detail-name">
                                    Date Created
                                    <span class="AdminTransactionRecord__detail-name-dash">:</span>
                                </p>
                                <p class="AdminTransactionRecord__detail-value" id="modalTransactionCreated">Apr 10, 2025 - 12:02 AM</p>
                            </div>
                        </div>
                        <div class="AdminTransactionRecord__top-details-container">
                            <div class="AdminTransactionRecord__details-list">
                                <p class="AdminTransactionRecord__detail-name1">
                                    Transaction ID
                                    <span class="AdminTransactionRecord__detail-name-dash">:</span>
                                </p>
                                <p class="AdminTransactionRecord__detail-value" ><span id="modalTransactionId">1</span></p>
                            </div>
                            <div class="AdminTransactionRecord__details-list">
                                <p class="AdminTransactionRecord__detail-name">
                                    Payment Method
                                    <span class="AdminTransactionRecord__detail-name-dash">:</span>
                                </p>
                                <p class="AdminTransactionRecord__detail-value" id="modalPaymentMethod">GCash <span class="AdminTransactionRecord__reference-number" id="modalReferenceNumber">REF20250410A</span></p>
                            </div>
                        </div>

                    </div>

                    <div class="AdminTransactionRecord__modal-bottom">
                        <div class="AdminTransactionRecord__transaction-items-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Item Category</th>
                                        <th>Item Size</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Item Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
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

        <script src="../../assets/js/admin/admin_transaction_record.js"></script>
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