<?php
    // Start the session
    session_start();

    include "../../config/db.php";

    // Check if user is logged in and is an cashier
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
        header("Location: ../../pages/auth/cashier_login.php");
        exit();
    }

    // Handle logout
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect to login page
        header("Location: ../../pages/auth/cashier_login.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['action']) && $_GET['action'] === 'donePayment') {
        
        // Get data from form
        $cartData = json_decode($_POST['cart_data'], true);
        $paymentAmount = floatval($_POST['payment_amount']);
        $paymentMethod = $_POST['payment_method'];
        $referenceNumber = $_POST['reference_number'] ?? null;
        $totalAmount = floatval($_POST['total_amount']);
        $changeAmount = floatval($_POST['change_amount']);
        $cashierId = $_SESSION['user_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Insert into transactions table    
            $transactionSql = "INSERT INTO transactions 
                            (user_id, total_amount, payment_method, ref_no) 
                            VALUES (?, ?, ?, ?)";
            
            $transactionStmt = $conn->prepare($transactionSql);
            $transactionStmt->bind_param("idss", 
                $cashierId, 
                $totalAmount, 
                $paymentMethod, 
                $referenceNumber
            );
            
            $transactionStmt->execute();
            $transactionId = $conn->insert_id;
            
            // 2. Insert each item into transaction_items table
            foreach ($cartData as $item) {
                $productId = $item['id'];
                $productSize = $item['size'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $subtotal = $price * $quantity;
                
                // Get the variant ID based on product ID and size
                $variantSql = "SELECT id FROM product_variants WHERE product_id = ? AND item_size = ?";
                $variantStmt = $conn->prepare($variantSql);
                $variantStmt->bind_param("is", $productId, $productSize);
                $variantStmt->execute();
                $variantResult = $variantStmt->get_result();
                
                if ($variantRow = $variantResult->fetch_assoc()) {
                    $variantId = $variantRow['id'];
                    
                    // Insert into transaction_items
                    $itemSql = "INSERT INTO transaction_items 
                                (transaction_id, product_id, product_variant_id, quantity, unit_price, total_price) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                    
                    $itemStmt = $conn->prepare($itemSql);
                    $itemStmt->bind_param("iiiddd", 
                        $transactionId, 
                        $productId, 
                        $variantId, 
                        $quantity, 
                        $price, 
                        $subtotal
                    );
                    
                    $itemStmt->execute();
                    
                    // Update product stock
                    $updateStockSql = "UPDATE product_variants 
                                    SET item_stock = item_stock - ? 
                                    WHERE id = ?";
                    
                    $updateStockStmt = $conn->prepare($updateStockSql);
                    $updateStockStmt->bind_param("ii", $quantity, $variantId);
                    $updateStockStmt->execute();
                }
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Return success response for AJAX
            echo json_encode([
                'status' => 'success',
                'message' => 'Transaction completed successfully.',
                'transaction_id' => $transactionId
            ]);
            exit();
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            
            // Return error response for AJAX
            echo json_encode([
                'status' => 'error',
                'message' => 'Transaction failed: ' . $e->getMessage()
            ]);
            exit();
        }

    }

    $userId = $_SESSION['user_id'] ?? null;
    $cashierFullName = 'NONE';
    $role = 'NONE';
    $avatarPath = '../../assets/images/avatars/default.jpg'; 

    if ($userId) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("
        SELECT 
            id, 
            firstname, 
            middlename, 
            lastname, 
            username, 
            role, 
            gender, 
            birth_year, 
            birth_month, 
            birth_day, 
            created_at, 
            image 
        FROM users WHERE id = ?");

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
          
            $cashierName = $row['firstname'] . ' ' . $row['lastname'];
            $role = $row['role'];
            
            $birthDate = new DateTime($row['birth_year'] . '-' . $row['birth_month'] . '-' . $row['birth_day']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            $birthdate = date("F d, Y", strtotime("{$row['birth_year']}-{$row['birth_month']}-{$row['birth_day']}"));
            
            $created_at_formatted = date("F d, Y — h:i A", strtotime($row['created_at']));
            
            if (!empty($row['image'])) {
                $avatarPath = '../../assets/images/avatars/' . $row['image'];
            }
            
            $fullname = ucwords(strtolower($row['firstname'])) . ' ' .
                       (isset($row['middlename']) ? ucwords(strtolower($row['middlename'])) . ' ' : '') .
                       ucwords(strtolower($row['lastname']));
            
            $modalData = [
                'id' => $row['id'],
                'username' => $row['username'],
                'fullname' => $fullname,
                'gender' => ucwords(strtolower($row['gender'])),
                'age' => $age,
                'birthdate' => $birthdate,
                'created_at' => $created_at_formatted,
                'image' => $avatarPath
            ];
        }
        
        $stmt->close();
    }
    
    // Function to get products by category
    function getProductsByCategory($conn, $category) {
        $products = [];
        
        $sql = "SELECT p.id, p.item_name, p.item_image, pv.item_size, pv.item_price, pv.item_stock 
                FROM products p 
                JOIN product_variants pv ON p.id = pv.product_id 
                WHERE p.item_category = ? AND p.status = 'active'
                ORDER BY p.item_name DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $productId = $row['id'];
            
            // Check if this product is already in our array
            if (!isset($products[$productId])) {
                $products[$productId] = [
                    'id' => $productId,
                    'name' => $row['item_name'],
                    'image' => $row['item_image'],
                    'sizes' => []
                ];
            }
            
            // Add this size variant
            $products[$productId]['sizes'][] = [
                'size' => $row['item_size'],
                'price' => $row['item_price'],
                'stock' => $row['item_stock']
            ];
        }
        
        $stmt->close();
        return $products;
    }
    
    // Get all categories for the main menu
    $categories = [
        'iced' => [
            'Iced Coffee' => [],
            'Iced Non-Coffee' => []
        ],
        'hot' => [
            'Hot Coffee' => [],
            'Hot Non-Coffee' => []
        ],
        'frappe' => [
            'Frappe Coffee' => [],
            'Frappe Non-Coffee' => []
        ],
        'meal' => [
            'Rice Meal' => [],
            'Ala Carte' => [],
            'Croffle' => [],
            'Pasta & Fries' => [],
            'Pizza Barkada' => []
        ],
        'add-ons' => [
            'Add-Ons' => []
        ]
    ];
    
    // Fetch products for all categories
    foreach ($categories as $mainCategory => $subCategories) {
        foreach ($subCategories as $subCategory => $products) {
            $categories[$mainCategory][$subCategory] = getProductsByCategory($conn, $subCategory);
        }
    }
    
    // Default selected category
    $selectedCategory = 'iced';
    
    if (isset($_GET['category'])) {
        $selectedCategory = $_GET['category'];
    }
    
    $conn->close();
?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cashier Dashboard | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/cashier/cashier_dashboarda.css">
        <link rel="stylesheet" href="../../assets/css/modall.css">
    </head>
    <body>
        <div class="CashierDashboard__main-content">

            <div class="CashierDashboard__main-section">
                <div class="CashierDashboard__branding">
                    <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="CashierDashboard__logo" />
                    <div class="CashierDashboard__system-name">
                        <p class="CashierDashboard__title">JoeBean</p>
                        <p class="CashierDashboard__subtitle">Point-of-Sale System with Inventory</p>
                    </div>
                </div>
                <div class="CashierDashboard__menu-section">
                    <div class="CashierDashboard__menu-sidebar">
                        <div class="CashierDashboard__menu-button-wrapper">
                            <p>Menu</p>
                            <div class="CashierDashboard__menu-button-container">

                                <button 
                                class="CashierDashboard__category-button <?php echo $selectedCategory === 'iced' ? 'selected' : ''; ?>"
                                data-category="iced"
                                data-selected-img="../../assets/images/selected-cup-coffee.png"
                                data-unselected-img="../../assets/images/unselected-cup-coffee.png">
                                    <img 
                                    class="CashierDashboard__category-button-img" 
                                    src="../../assets/images/<?php echo $selectedCategory === 'iced' ? 'selected' : 'unselected'; ?>-cup-coffee.png"
                                    alt=" cup coffee">
                                </button>

                                <button 
                                class="CashierDashboard__category-button <?php echo $selectedCategory === 'hot' ? 'selected' : ''; ?>"
                                data-category="hot"
                                data-selected-img="../../assets/images/selected-coffee.png"
                                data-unselected-img="../../assets/images/unselected-coffee.png">
                                    <img 
                                    class="CashierDashboard__category-button-img" 
                                    src="../../assets/images/<?php echo $selectedCategory === 'hot' ? 'selected' : 'unselected'; ?>-coffee.png"
                                    alt="hot coffee">
                                </button>

                                <button 
                                class="CashierDashboard__category-button <?php echo $selectedCategory === 'frappe' ? 'selected' : ''; ?>"
                                data-category="frappe"
                                data-selected-img="../../assets/images/selected-shake.png"
                                data-unselected-img="../../assets/images/unselected-shake.png">
                                    <img 
                                    class="CashierDashboard__category-button-imgs" 
                                    src="../../assets/images/<?php echo $selectedCategory === 'frappe' ? 'selected' : 'unselected'; ?>-shake.png"
                                    alt="shake">
                                </button>

                                <button 
                                class="CashierDashboard__category-button <?php echo $selectedCategory === 'meal' ? 'selected' : ''; ?>"
                                data-category="meal"
                                data-selected-img="../../assets/images/selected-meal.png"
                                data-unselected-img="../../assets/images/unselected-meal.png">
                                    <img 
                                    class="CashierDashboard__category-button-img" 
                                    src="../../assets/images/<?php echo $selectedCategory === 'meal' ? 'selected' : 'unselected'; ?>-meal.png"
                                    alt="meal">
                                </button>

                                <button 
                                class="CashierDashboard__category-button <?php echo $selectedCategory === 'add-ons' ? 'selected' : ''; ?>"
                                data-category="add-ons"
                                data-selected-img="../../assets/images/selected-add-ons.png"
                                data-unselected-img="../../assets/images/unselected-add-ons.png">
                                    <img 
                                    class="CashierDashboard__category-button-img" 
                                    src="../../assets/images/<?php echo $selectedCategory === 'add-ons' ? 'selected' : 'unselected'; ?>-add-ons.png" 
                                    alt="add ons">
                                </button>
                              
                            </div>
                        </div>
                    </div>
                    <div class="CashierDashboard__menu-items-section">
                        <div class="CashierDashboard__menu-items-wrapper">

                            <!-- <div class="CashierDashboard__each-menu-sub-category-container">
                                <h2>Iced Coffee</h2>
                                <div class="CashierDashboard__wrap-menu-sub-category-content">

                                    <div class="CashierDashboard__product-item-container">
                                        <div class="CashierDashboard__product-top-content">
                                            <h3>SPANISH LATTE</h3>
                                            <img src="../../assets/images/products/1744201275_spanish-latte.png" alt="">
                                        </div>
                                        <div class="CashierDashboard__product-bottom-content">
                                            <div class="CashierDashboard__product-size-container">
                                                <p class="CashierDashboard__product-price">₱<span>119</span></p>
                                                <button class="CashierDashboard__size-btn">Grande</button>
                                                <span class="CashierDashboard__product-stock">Stk: <span>29</span></span>
                                            </div>
                                            <div class="CashierDashboard__product-size-container">
                                                <p class="CashierDashboard__product-price">₱<span>119</span></p>
                                                <button class="CashierDashboard__size-btn">Venti</button>
                                                <span class="CashierDashboard__product-stock">Stk: <span>29</span></span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="CashierDashboard__each-menu-sub-category-container">
                                <h2>Iced Non-Coffee</h2>
                                <div class="CashierDashboard__wrap-menu-sub-category-content">

                                    <div class="CashierDashboard__product-item-container">
                                        <div class="CashierDashboard__product-top-content">
                                            <h3>SPANISH LATTE</h3>
                                            <img src="../../assets/images/products/1744201275_spanish-latte.png" alt="">
                                        </div>
                                        <div class="CashierDashboard__product-bottom-content">
                                            <div class="CashierDashboard__product-size-container">
                                                <p class="CashierDashboard__product-price">₱<span>119</span></p>
                                                <button class="CashierDashboard__size-btn">Grande</button>
                                                <span class="CashierDashboard__product-stock">Stk: <span>29</span></span>
                                            </div>
                                            <div class="CashierDashboard__product-size-container">
                                                <p class="CashierDashboard__product-price">₱<span>119</span></p>
                                                <button class="CashierDashboard__size-btn">Venti</button>
                                                <span class="CashierDashboard__product-stock">Stk: <span>29</span></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div> -->

                            <?php 
                            // Display products for the selected main category
                            if (isset($categories[$selectedCategory])) {
                                foreach ($categories[$selectedCategory] as $subCategory => $products) {
                                    if (!empty($products)) {
                            ?>
                            <div class="CashierDashboard__each-menu-sub-category-container">
                                <h2><?php echo $subCategory; ?></h2>
                                <div class="CashierDashboard__wrap-menu-sub-category-content">
                                    <?php foreach ($products as $product) { ?>
                                        <div class="CashierDashboard__product-item-container">
                                            <div class="CashierDashboard__product-top-content">
                                                <h3><?php echo strtoupper($product['name']); ?></h3>
                                                <img src="../../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                            </div>
                                            <div class="CashierDashboard__product-bottom-content">
                                                <?php 
                                                    foreach ($product['sizes'] as $size) { 
                                                    
                                                    if (empty($size['size']) && $size['price'] == 0 && $size['stock'] == 0) {
                                                        continue;
                                                    }    
                                                ?>
                                                    <div class="CashierDashboard__product-size-container">
                                                        <p class="CashierDashboard__product-price">₱<span><?php echo number_format($size['price'], 0); ?></span></p>
                                                            <button class="CashierDashboard__size-btn" 
                                                                    data-product-id="<?php echo $product['id']; ?>"
                                                                    data-product-name="<?php echo $product['name']; ?>"
                                                                    data-product-size="<?php echo $size['size']; ?>"
                                                                    data-product-price="<?php echo $size['price']; ?>"
                                                                    data-subcategory="<?php echo $subCategory; ?>">
                                                                <?php echo (!$size['size']) ? 'ADD' : $size['size']; ?>
                                                            </button>
                                                        <span class="CashierDashboard__product-stock">Stk: <span><?php echo $size['stock']; ?></span></span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                                <?php 
                                    } else {
                                ?>
                            <div class="CashierDashboard__each-menu-sub-category-container">
                                <h2><?php echo $subCategory; ?></h2>
                                <div class="CashierDashboard__wrap-menu-sub-category-content-no-product">
                                    <p class="CashierDashboard__no-products">No products available.</p>
                                </div>
                            </div>
                            <?php
                                    }
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="CashierDashboard__order-section">
                <div class="CashierDashboard__cashier-details-container">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="<?php echo htmlspecialchars($cashierName); ?>">
                    <div class="CashierDashboard__cashier-name">
                        <span><?php echo htmlspecialchars($cashierName); ?></span>
                        <span><?php echo htmlspecialchars($role); ?></span>
                    </div>
                    <button class="CashierDashboard__modal-dots-btn">•••</button>
                </div>
                <div class="CashierDashboard__order-list-container">
                    <div class="CashierDashboard__order-list-wrapper">
                         
                        <!--<div class="CashierDashboard__order-selected-container">
                            <div class="CashierDashboard__order-item-name-container">
                                <span>Spanish Latte</span>
                                <div class="CashierDashboard__order-item-size-price-container">
                                    <p>Venti</p>/<p>₱89.00</p>
                                </div>
                            </div>
                            <div class="CashierDashboard__order-item-price-delete-container">
                                <span>2</span>
                                <div class="CashierDashboard__order-line"></div>
                                <div class="CashierDashboard__order-delete-icon-container">
                                    <img class="CashierDashboard__order-delete-icon" src="../../assets/images/trashcan-icon.svg" alt="trashcan icon">
                                </div>    
                            </div>
                        </div> -->

                    </div>
                </div>
                <div class="CashierDashboard__place-order-container">
                    <div class="CashierDashboard__total-container">
                        <span>Total</span>
                        <span>:</span>
                        <span>₱ 0.00</span>
                    </div>
                    <button class="CashierDashboard__place-order-btn">
                        Place Order
                    </button>
                </div>
            </div>
        </div>



        <!-- profile details modal structure -->
        <div class="modal" id="profileDetailsModal">
            <div class="modal-content CashierDashboard__relative-modal">
                <div class="CashierDashboard__modal-header">
                    <span class="close" id="closeModal">&times;</span>
                    <h3>Profile Details</h3>
    
                </div>

                <div class="CashierDashboard__modal-form-container">
                    <div class="CashierDashboard__modal-left">
                        <img id="modalImage" src="<?php echo htmlspecialchars($modalData['image'] ?? '../../assets/images/image-preview.jpg'); ?>" alt="<?php echo htmlspecialchars($cashierName); ?>" class="CashierDashboard__item-image-icon">
                    </div>

                    <div class="CashierDashboard__modal-right">
                        <p class="CashierDashboard__fullname" id="modalFullname">
                            <?php echo htmlspecialchars($modalData['fullname'] ?? '-'); ?>
                        </p>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Cashier ID <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalUsername">
                                <?php echo htmlspecialchars($modalData['id'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Username <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalUsername">
                                <?php echo htmlspecialchars($modalData['username'] ?? '-'); ?></p>
                            </p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Gender <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalGender">
                                <?php echo htmlspecialchars($modalData['gender'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Age <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalAge">
                                <?php echo htmlspecialchars($modalData['age'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Birthdate <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalBirthdate">
                                <?php echo htmlspecialchars($modalData['birthdate'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Account Created <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalCreatedAt">
                                <?php echo htmlspecialchars($modalData['created_at'] ?? '-'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="CashierDashboard__modal-footer-container">
                    <button type="button" class="CashierDashboard__modal-logout-button" id="logoutBtnModal">
                        Logout
                    </button>
                </div>
            </div>
        </div>



        <!--place order modal structure -->
        <div class="modal" id="placeOrderModal">
            <form id="transactionForm" method="POST" class="CashierDashboard__place-order-modal-content">
                            
                <input type="hidden" name="cart_data" id="cartDataField">
                <input type="hidden" name="payment_amount" id="paymentAmountField">
                <input type="hidden" name="payment_method" id="paymentMethodField">
                <input type="hidden" name="reference_number" id="referenceNumberField">
                <input type="hidden" name="total_amount" id="totalAmountField">
                <input type="hidden" name="change_amount" id="changeAmountField">   
                <!-- Left Section - Order Details -->
                <div class="CashierDashboard__order-section-modal">
                    <h2 class="CashierDashboard__section-title">TOTAL ITEMS</h2>
                    <div class="CashierDashboard__final-order-content">
                        <div class="CashierDashboard__item-title-container">
                            <div class="CashierDashboard__item-qty-title">Qty.</div>
                            <div class="CashierDashboard__item-product-title">Product</div>
                            <div class="CashierDashboard__item-price-title">Price</div>
                        </div>
                        <div class="CashierDashboard__order-for-content">
                            <div class="CashierDashboard__order-wrapper">
                                <!-- <div class="CashierDashboard__order-items">
                                    <div class="CashierDashboard__item-qty-value">1</div>
                                    <div class="CashierDashboard__item-product-value-content">
                                        <p>Spanish Latte</p>
                                        <div class="CashierDashboard__item-product-sub-content">
                                            <span>Venti</span>
                                        </div>
                                    </div>
                                    <div class="CashierDashboard__item-price-value">₱89.00</div>
                                </div>-->
                                
                            </div>
                        </div>      
                    </div>
                </div>
                
                <!-- Right Section - Payment -->
                <div class="CashierDashboard__payment-section">
                    <h2 class="CashierDashboard__section-title">PAYMENT</h2>
                    
                    <div class="CashierDashboard__payment-details">
                        <div class="CashierDashboard__payment-row">
                            <div class="CashierDashboard__payment-label">TOTAL</div>
                            <span>:</span>
                            <div class="CashierDashboard__payment-value">0.00</div>
                        </div>
                        <div class="CashierDashboard__payment-row">
                            <div class="CashierDashboard__payment-label">PAYMENT</div>
                            <span>:</span>
                            <div class="CashierDashboard__payment-value">0.00</div>
                        </div>
                        <div class="CashierDashboard__payment-rows">
                            <div class="CashierDashboard__payment-label">CHANGE</div>
                            <span>:</span>
                            <div class="CashierDashboard__payment-value change-value">0.00</div>
                        </div>
                        
                        <input type="text" class="CashierDashboard__payment-input" value="">
                        
                        <div class="CashierDashboard__numpad">
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="1">1</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="2">2</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="3">3</button>
                            <button type="button" class="CashierDashboard__numpad-btn function-btn" data-key="+10">+10</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="4">4</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="5">5</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="6">6</button>
                            <button type="button" class="CashierDashboard__numpad-btn function-btn" data-key="+20">+20</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="7">7</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="8">8</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="9">9</button>
                            <button type="button" class="CashierDashboard__numpad-btn function-btn" data-key="+50">+50</button>
                            <button type="button" class="CashierDashboard__numpad-btn function-btn" data-key="+/-">+/-</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key="0">0</button>
                            <button type="button" class="CashierDashboard__numpad-btn" data-key=".">.</button>
                            <button type="button" class="CashierDashboard__numpad-btn function-btn" data-key="backspace">
                                <img src="../../assets/images/numpad-delete-icon.svg" alt="numpad delete icon">
                            </button>
                        </div>
                        
                        <div class="CashierDashboard__action-buttons">
                            <button  type="button"  class="btn CashierDashboard__btn-cash">
                                CASH
                                <div class="CashierDashboard__check-payment">
                                    <img src="../../assets/images/check-icon.svg" alt="check icon">
                                </div>
                            </button>
                            <div class="CashierDashboard__done-cancel-button">
                                <button type="submit" class="btn CashierDashboard__btn-done">DONE</button>
                                <button type="button" class="btn CashierDashboard__btn-cancel">CANCEL</button>
                            </div>
                        </div>
                        
                        <div class="CashierDashboard__payment-method">
                            <button type="button" class="btn CashierDashboard__btn-gcash">
                                GCash
                                <div class="CashierDashboard__check-payment">
                                    <img src="../../assets/images/check-icon.svg" alt="check icon">
                                </div>
                            </button>
                            <!-- <input type="text" class="ref-input" placeholder="REF NO."> -->
                            <div class="CashierDashboard__ref-input-group">
                                <input type="text" name="username" id="username" placeholder="" autocomplete="off" required />
                                <label for="username">REF NO.</label>
                            </div>
                        </div>
                    </div>
                </div>               
            </form>
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
                    <a href="cashier_dashboard.php?action=logout" class="Logout__modal-confirm-button">
                        Confirm
                    </a>
                </div>
            </div>
        </div>

        <div class="modal" id="ErrorPaymentModal">
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

        <div class="modal" id="receiptModal">
            <div class="Modal_fade-in Receipt__modal-content">
                <div class="Receipt__modal-header">
                    <h3>Transaction Receipt</h3>
                </div>
                <div class="Receipt__modal-body">
                    <div class="Receipt__modal-body-container">
                        <div class="Receipt__branding">
                            <img src="../../assets/images/joebean-logo.png" alt="JoeBean Logo" class="Receipt__logo" />
                            <div class="Receipt__company-info">
                                <h4>JoeBean Coffee Shop</h4>
                                <p>123 Coffee Street, Bean City</p>
                                <p>Cel: 0912-345-6789</p>
                            </div>
                        </div>
                        <div class="Receipt__details">
                            <div class="Receipt__transaction-info">
                                <p>Transaction #: <span id="receiptTransactionId"></span></p>
                                <p>Date: <span id="receiptDate"></span></p>
                                <p>Cashier: <span id="receiptCashier"></span></p>
                            </div>
                            <div class="Receipt__items-container">
                                <table class="Receipt__items-table">
                                    <thead>
                                        <tr>
                                            <th>Qty</th>
                                            <th>Item</th>
                                            <th>Size</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="receiptItemsList">
                                        <!-- <tr>
                                            <td>1</td>
                                            <td>Spanish latte</td>
                                            <td>Venti</td>
                                            <td>₱89.00</td>
                                            <td>₱89.00</td>
                                        </tr> -->
                                        
                                    </tbody>
                                </table>
                            </div>
                            <div class="Receipt__summary">
                                <div class="Receipt__summary-row">
                                    <span>Total:</span>
                                    <span id="receiptTotal"></span>
                                </div>
                                <div class="Receipt__summary-row">
                                    <span>Payment Method:</span>
                                    <span id="receiptPaymentMethod"></span>
                                </div>
                                <div class="Receipt__summary-row" id="receiptRefRow">
                                    <span>Reference No:</span>
                                    <span id="receiptRefNo"></span>
                                </div>
                                <div class="Receipt__summary-row">
                                    <span>Amount Paid:</span>
                                    <span id="receiptAmountPaid"></span>
                                </div>
                                <div class="Receipt__summary-row">
                                    <span>Change:</span>
                                    <span id="receiptChange"></span>
                                </div>
                            </div>
                            <div class="Receipt__footer">
                                <p>Thank you for your purchase!</p>
                                <p>Please come again</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="Receipt__modal-footer">
                    <button class="Receipt__print-btn">Print Receipt</button>
                    <button class="Receipt__close-btn">Close</button>
                </div>
            </div>
        </div>

        <script src="../../assets/js/cashier/cashier_dashboard.js"></script>
    
    </body>
</html>