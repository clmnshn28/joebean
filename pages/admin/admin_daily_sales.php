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

        // Handle AJAX request for daily transactions
        if(isset($_POST['action']) && $_POST['action'] == 'get_daily_transactions') {

            $date = mysqli_real_escape_string($conn, $_POST['date']);

            // Query to get all transactions for the specified date
            $sales_query =  mysqli_query($conn,"
                SELECT 
                    t.id,
                    t.total_amount,
                    t.payment_method,
                    t.ref_no,
                    t.created_at,
                    CONCAT(u.firstname, ' ', u.lastname) AS cashier_name,
                    u.image as cashier_image
                FROM 
                    transactions t
                LEFT JOIN 
                    users u ON t.user_id = u.id
                WHERE 
                    DATE(t.created_at) = '$date'
                ORDER BY 
                    t.created_at DESC
            ");

            // Fetch all rows as an array
            $transaction_sales = [];
            while($row = mysqli_fetch_assoc($sales_query)) {
                $transaction_sales[] = $row;
            }

            $result = [
                'transactions' => $transaction_sales,
            ];
            
            echo json_encode($result);
            exit();
        }


        // Export to Excel functionality
        if (isset($_POST['export_excel'])) {
            $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM transactions");
            $count_data = mysqli_fetch_assoc($count_query);


            if ($count_data['count'] == 0) {
                $_SESSION['export_error'] = "Unable to export data to Excel. There are no items available to export.";
                header("Location: " . $_SERVER['PHP_SELF']); // Redirect back to the same page
                exit();
            } else {
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="daily_sales_records_' . date('Y-m-d') . '.xls"');
                date_default_timezone_set('Asia/Manila');

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
                            <th colspan="8" style="font-size: 16pt; text-align: center; background-color: #656D4A; color: white;">JoeBean Sales Records</th>
                        </tr>
                        <tr>
                            <th colspan="8" style="font-size: 11pt; text-align: center;">Generated on: ' . date('Y-m-d - h:i A') . '</th>
                        </tr>
                        <tr>
                            <th style="background-color: #656D4A; color: white;">Sales ID</th>
                            <th style="background-color: #656D4A; color: white;">Total Sales</th>
                            <th style="background-color: #656D4A; color: white;">Day</th>
                            <th style="background-color: #656D4A; color: white;">Cashier</th>
                            <th style="background-color: #656D4A; color: white;">Total Price</th>
                            <th style="background-color: #656D4A; color: white;">Payment Method</th>   
                            <th style="background-color: #656D4A; color: white;">Reference No.</th>                        
                            <th style="background-color: #656D4A; color: white;">Date</th>
                        </tr>';

                        // Get all daily sales records
                        $daily_sales_query = mysqli_query($conn, "
                        SELECT 
                            DATE(created_at) as sale_date,
                            DAYNAME(created_at) as day_of_week,
                            SUM(total_amount) as daily_total,
                            COUNT(*) as transaction_count
                        FROM transactions
                        GROUP BY DATE(created_at)
                        ORDER BY sale_date DESC
                        ");

                        $sales_id = 1; // Counter for sales ID

                        while ($daily_row = mysqli_fetch_assoc($daily_sales_query)) {
                            $date = $daily_row['sale_date'];
                            $dayOfWeek = $daily_row['day_of_week'];
                            $dailyTotal = $daily_row['daily_total'];
                            
                            // Get all transactions for this date
                            $transactions_query = mysqli_query($conn, "
                                SELECT 
                                    t.id,
                                    t.total_amount,
                                    t.payment_method,
                                    t.ref_no,
                                    t.created_at,
                                    CONCAT(u.firstname, ' ', u.lastname) AS cashier_name
                                FROM 
                                    transactions t
                                LEFT JOIN 
                                    users u ON t.user_id = u.id
                                WHERE 
                                    DATE(t.created_at) = '$date'
                                ORDER BY 
                                    t.created_at DESC
                            ");
                            
                            $transaction_count = mysqli_num_rows($transactions_query);
                            
                            // Daily sale header
                            echo '<tr class="transaction-header">';
                            echo '<td rowspan="' . ($transaction_count + 2) . '" style="text-align: center; vertical-align: middle; font-weight: bold;">' . $sales_id . '</td>';
                            echo '<td rowspan="' . ($transaction_count + 2) . '" style="text-align: center; vertical-align: middle;">&#8369; ' . number_format($dailyTotal, 2) . '</td>';
                            echo '<td rowspan="' . ($transaction_count + 2) . '" style="text-align: center; vertical-align: middle;">' . $dayOfWeek . '</td>';
                            echo '<td colspan="4" style="background-color:rgba(194, 197, 170, 0.63);"><strong>Daily Transactions:</strong></td>';
                            echo '<td rowspan="' . ($transaction_count + 2) . '" style="text-align: center; vertical-align: middle;">' . date('m/d/Y', strtotime($date)) . '</td>';
                            echo '</tr>';
                            
                            // Individual transactions
                            while ($trans = mysqli_fetch_assoc($transactions_query)) {
                                $cashier_name = htmlspecialchars(ucwords(strtolower($trans['cashier_name'])));
                                $payment_method = htmlspecialchars($trans['payment_method']);
                                $ref_no = htmlspecialchars($trans['ref_no']);
                                $ref_display = $ref_no ? $ref_no : '-';
                                
                                echo '<tr class="item-row">';
                                    echo '<td>' . $cashier_name . '</td>';
                                    echo '<td>&#8369;' . number_format($trans['total_amount'], 2) . '</td>';
                                    echo '<td style="text-align: center; ">' . $payment_method . '</td>';
                                    echo '<td style="text-align: center; ">' . $ref_display . '</td>';   
                                echo '</tr>';
                            }
                            
                            // Daily total row
                            echo '<tr class="total-row">';
                            echo '<td colspan="3" style="text-align: right;">Total Transactions:</td>';
                            echo '<td>' . $transaction_count . '</td>';
                            echo '</tr>';
                            
                            // Separator row
                            echo '<tr><td colspan="8" class="transaction-separator"></td></tr>';
                            
                            $sales_id++; // Increment sales ID counter
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
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM (
                        SELECT DATE(created_at) as sale_date 
                        FROM transactions 
                        WHERE DATE(created_at) LIKE '%$search_term%'
                        GROUP BY DATE(created_at)
                    ) as daily_counts");
                    
        $total_transactions = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_transactions / $limit);

        // Fetch paginated transaction records with user and product info
        $result = mysqli_query($conn, "
                SELECT 
                    DATE(created_at) as sale_date,
                    DAYNAME(created_at) as day_of_week,
                    SUM(total_amount) as daily_total,
                    COUNT(*) as transaction_count
                FROM transactions
                WHERE DATE(created_at) LIKE '%$search_term%'
                GROUP BY DATE(created_at)
                ORDER BY sale_date DESC
                LIMIT $offset, $limit
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
            <link rel="stylesheet" href="../../assets/css/admin/admin_daily_sale.css">
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
                        <a href="admin_transaction_record.php">
                            <img src="../../assets/images/time-icon.svg" alt="time-icon">
                            Transaction Records
                        </a>
                        <a href="admin_daily_sales.php" class="active">
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
                            <h3>Daily Sales</h3>
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
                        <table class="AdminDailySales__table-content-item">
                            <thead>
                                <tr>
                                    <th>Total Sales</th>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="transactionTableBody">
                            <!-- <tr>
                                    <td>₱ 10,000.00</td>
                                    <td>Monday</td>
                                    <td>05/04/2025</td>
                                    <td>
                                        <button class="AdminTransactionRecord__table-data-btn" id="viewButton">
                                            view
                                        </button>  
                                    </td>
                                </tr> -->
                                <?php
                                    if(mysqli_num_rows($result) > 0) {
                                        while($row = mysqli_fetch_assoc($result)) {
                                            $formatted_date = date('m/d/Y', strtotime($row['sale_date']));
                                            echo "<tr>";
                                            echo "<td>₱ " . number_format($row['daily_total'], 2) . "</td>";
                                            echo "<td>" . $row['day_of_week'] . "</td>";
                                            echo "<td>" . $formatted_date . "</td>";
                                            echo "<td>
                                                    <button class='AdminTransactionRecord__table-data-btn viewButton' data-date='" . $row['sale_date'] . "'>
                                                        view
                                                    </button>  
                                                </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo '<tr class="no-data-row"><td colspan="8"><div class="no-data-message">No Sales Records </div></td></tr>';
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
                        <h3>Daily Sales Details</h3>
                    </div>
                    <div class="AdminDailySales__modal-form-container">
            

                        <div class="AdminDailySales__modal-bottom">
                            <div class="AdminDailySales__details-list">
                                <p class="AdminDailySales__detail-name1">
                                    Sales ID
                                    <span class="AdminDailySales__detail-name-dash">:</span>
                                </p>
                                <p class="AdminDailySales__detail-value" ><span id="modalSalesId">1</span></p>
                            </div>
                            <div class="AdminDailySales__transaction-items-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Cashier</th>
                                            <th>Total Price</th>
                                            <th>Payment Method</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class='AdminDailySales__modal-item-with-image'>
                                                    <img class='AdminTransactionRecords__item-image' src='../../assets/images/avatars/default.jpg' alt='Cashier Image'>
                                                    <span>Celmin Shane Quizon</span>
                                                </div> 
                                            </td>
                                            <td>₱1000.00</td>
                                            <td>
                                                <div class='AdminTransactionRecord__item-with-references'>
                                                    <p>GCash</p>
                                                    <p class='AdminTransactionRecord__item-reference-number' id='modalReferenceNumber'>
                                                    URHFSUWROT
                                                    </p>
                                                </div>
                                            </td>
                                            <td>05/04/2025</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class='AdminDailySales__modal-item-with-image'>
                                                    <img class='AdminTransactionRecords__item-image' src='../../assets/images/avatars/default.jpg' alt='Cashier Image'>
                                                    <span>Celmin Shane Quizon</span>
                                                </div> 
                                            </td>
                                            <td>₱1000.00</td>
                                            <td>
                                                <div class='AdminTransactionRecord__item-with-references'>
                                                    <p>Cash</p>
                                                </div>
                                            </td>
                                            <td>05/04/2025</td>
                                        </tr>
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

            <script src="../../assets/js/admin/admin_daily_sale.js"></script>
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