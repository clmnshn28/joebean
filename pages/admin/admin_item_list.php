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

$error_message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'adding-data') {
    $itemName = $_POST["item-name"];
    $itemCategory = $_POST["item-category"];
    $itemPrice1 = $_POST["item-price1"] !== "" ? $_POST["item-price1"] : null;
    $itemPrice2 = $_POST["item-price2"] !== "" ? $_POST["item-price2"] : null;
    $itemStock1 = $_POST["item-stock1"] !== "" ? $_POST["item-stock1"] : null;
    $itemStock2 = $_POST["item-stock2"] !== "" ? $_POST["item-stock2"] : null;
    $itemSize1 = $_POST["item-size1"] !== "" ? $_POST["item-size1"] : null;
    $itemSize2 = $_POST["item-size2"] !== "" ? $_POST["item-size2"] : null;
    

    // Image upload
    $imagePath = null;
    if (isset($_FILES['item-image']) && $_FILES['item-image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['item-image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['item-image']['name']);
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

    // Insert into product table
    $sqlProduct = "INSERT INTO products (user_id, item_name, item_category, item_image, status)
    VALUES (1, '$itemName', '$itemCategory', '$imagePath', 'active')";

    if (mysqli_query($conn, $sqlProduct)) {
    // Get the last inserted product_id
    $productId = mysqli_insert_id($conn);

    // Insert into product_variants table for each size, price, and stock combination
    $sqlVariant1 = "INSERT INTO product_variants (product_id, item_size, item_price, item_stock) 
                VALUES ($productId, '$itemSize1', '$itemPrice1', '$itemStock1')";

    $sqlVariant2 = "INSERT INTO product_variants (product_id, item_size, item_price, item_stock) 
                VALUES ($productId, '$itemSize2', '$itemPrice2', '$itemStock2')";

    if (mysqli_query($conn, $sqlVariant1) && mysqli_query($conn, $sqlVariant2)) {
        header("Location: admin_item_list.php");
        exit();
    } else {
        $error_message = "Error inserting product variants: " . mysqli_error($conn);
    }
    } else {
        $error_message = "Error inserting product: " . mysqli_error($conn);
    }
}

// Handle edit form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'editing-data') {
    $productId = $_POST["product-id"];
    $itemName = $_POST["item-edit-name"];
    $itemCategory = $_POST["item-edit-category"];
    $itemPrice1 = $_POST["item-edit-price1"] !== "" ? $_POST["item-edit-price1"] : null;
    $itemPrice2 = $_POST["item-edit-price2"] !== "" ? $_POST["item-edit-price2"] : null;
    $itemStock1 = $_POST["item-edit-stock1"] !== "" ? $_POST["item-edit-stock1"] : null;
    $itemStock2 = $_POST["item-edit-stock2"] !== "" ? $_POST["item-edit-stock2"] : null;
    $itemSize1 = $_POST["item-edit-size1"] !== "" ? $_POST["item-edit-size1"] : null;
    $itemSize2 = $_POST["item-edit-size2"] !== "" ? $_POST["item-edit-size2"] : null;
    
    // Image update logic
    $imagePath = $_POST["current-image"]; // Default to current image
    if (isset($_FILES['item-edit-image']) && $_FILES['item-edit-image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['item-edit-image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['item-edit-image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmpPath, $targetPath)) {
                $imagePath = $fileName;
            } else {
                $error_message = "Image upload failed.";
            }
        } else {
            $error_message = "Invalid image type.";
        }
    }

    // Update product in database
    $sqlProduct = "UPDATE products 
                  SET item_name = '$itemName', 
                      item_category = '$itemCategory', 
                      item_image = '$imagePath' 
                  WHERE id = $productId";

    if (mysqli_query($conn, $sqlProduct)) {
        // Get the variant IDs (you'll need to modify your query to include these)
        $variantQuery = "SELECT id FROM product_variants WHERE product_id = $productId ORDER BY id LIMIT 2";
        $variantResult = mysqli_query($conn, $variantQuery);
        $variantIds = [];
        while ($variant = mysqli_fetch_assoc($variantResult)) {
            $variantIds[] = $variant['id'];
        }
        
        // Update first variant
        if (isset($variantIds[0])) {
            $sqlVariant1 = "UPDATE product_variants 
                          SET item_size = '$itemSize1', 
                              item_price = '$itemPrice1', 
                              item_stock = '$itemStock1' 
                          WHERE id = {$variantIds[0]}";
            mysqli_query($conn, $sqlVariant1);
        }
        
        // Update second variant
        if (isset($variantIds[1])) {
            $sqlVariant2 = "UPDATE product_variants 
                          SET item_size = '$itemSize2', 
                              item_price = '$itemPrice2', 
                              item_stock = '$itemStock2' 
                          WHERE id = {$variantIds[1]}";
            mysqli_query($conn, $sqlVariant2);
        }
        
        header("Location: admin_item_list.php");
        exit();
    } else {
        $error_message = "Error updating product: " . mysqli_error($conn);
    }
}


// Handle item deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $productId = $_POST["delete_product_id"];
    
    // First delete the variants (to maintain foreign key constraints)
    $sqlDeleteVariants = "DELETE FROM product_variants WHERE product_id = $productId";
    
    if (mysqli_query($conn, $sqlDeleteVariants)) {
        // Then delete the product
        $sqlDeleteProduct = "DELETE FROM products WHERE id = $productId";
        
        if (mysqli_query($conn, $sqlDeleteProduct)) {
            header("Location: admin_item_list.php");
            exit();
        } else {
            $error_message = "Error deleting product: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Error deleting product variants: " . mysqli_error($conn);
    }
}

// Pagination logic
$items_per_page = 7;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Count total items for pagination
$count_query = "SELECT COUNT(*) as total FROM products WHERE status='active'";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_items = $count_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Modified query with LIMIT for pagination
$query = "
    SELECT 
        p.id, 
        p.item_name, 
        p.item_image, 
        p.item_category,
        GROUP_CONCAT(v.item_size SEPARATOR ', ') AS sizes,
        GROUP_CONCAT(v.item_price SEPARATOR ', ') AS prices,
        GROUP_CONCAT(v.item_stock SEPARATOR ', ') AS stocks
    FROM products p
    JOIN product_variants v ON p.id = v.product_id
    WHERE p.status = 'active'
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT $offset, $items_per_page
";

$result = mysqli_query($conn, $query);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Item List | JoeBean</title>
    <link rel="stylesheet" href="../../assets/css/indexs.css">
    <link rel="stylesheet" href="../../assets/css/admin/admin_item_lists.css">
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
                <a href="admin_item_list.php" class="active">
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
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                <?php endif; ?>
                <div class="AdminItemList__header-container">
                    <h3>Item List</h3>
                    <div class="AdminItemList__header-search-container">
                        <div class="AdminItemList__search-content">
                            <input type="text" autocomplete="off" placeholder="Search">
                            <span></span>
                            <img src="../../assets/images/search-icon.svg" alt="search icon">
                        </div>
                        <button id="openModalBtn"> + Add Item</button>
                    </div>
                </div>
                <table class="AdminItemList__table-content-item">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Size</th>
                            <th>Item Price</th>
                            <th>Item Category</th>
                            <th>Item Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody">
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>
                                        <div class='item-with-image'>
                                            <img class='item-image' src='../../assets/images/products/" . htmlspecialchars($row['item_image']) . "' alt='Item Image'>
                                            <span>" . htmlspecialchars(ucwords(strtolower($row['item_name']))) . "</span>
                                        </div>
                                    </td>";
                                // Size
                                $sizesRaw = trim($row['sizes'], ", ");
                                if ($sizesRaw === "") {
                                    echo "<td style='padding-left:30px'>-</td>";
                                } else {
                                    echo "<td>" . str_replace(",", "<br>", htmlspecialchars(ucwords(strtolower($sizesRaw)))) . "</td>";
                                }                         
                               // Price
                                $prices = array_filter(explode(',', $row['prices']), fn($p) => floatval($p) > 0);
                                $priceDisplay = implode("<br>₱", array_map('htmlspecialchars', $prices));
                                echo "<td>" . ($priceDisplay ? "₱" . $priceDisplay : "") . "</td>";
                                echo "<td>" . htmlspecialchars(ucwords(strtolower($row['item_category']))) . "</td>";
                                // Stock
                                $stocks = array_filter(explode(',', $row['stocks']), fn($s) => intval($s) > 0);
                                $stockDisplay = implode("<br>", array_map('htmlspecialchars', $stocks));
                                echo "<td>" . $stockDisplay . "</td>";
                                echo "<td>
                                        <div class='AdminItemList__table-btn'>
                                            <button 
                                                class='AdminItemList__table-edit-data-btn' 
                                                id='openEditModalBtn'
                                                data-product-id='" . $row['id'] . "' 
                                                data-name='" . htmlspecialchars($row['item_name']) . "'
                                                data-category='" . htmlspecialchars($row['item_category']) . "'
                                                data-sizes='" . htmlspecialchars($row['sizes']) . "'
                                                data-prices='" . htmlspecialchars($row['prices']) . "'
                                                data-stocks='" . htmlspecialchars($row['stocks']) . "'
                                                data-image='" . htmlspecialchars($row['item_image']) . "'
                                            >
                                                <img class='AdminItemList__table-edit-icon-btn'  src='../../assets/images/edit-icon.svg' alt='edit icon'>
                                            </button>
                                             <button 
                                                class='AdminItemList__table-delete-data-btn' 
                                                data-product-id='" . $row['id'] . "' 
                                                data-name='" . htmlspecialchars($row['item_name']) . "'
                                            >
                                                <img class='AdminItemList__table-delete-icon-btn' src='../../assets/images/trash-icon.svg' alt='trash icon'>
                                            </button>
                                        </div>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr class="no-data-row"><td colspan="5"><div class="no-data-message">No Item List</div></td></tr>';
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
        <form class="modal-content" action="" method="POST" enctype="multipart/form-data">
            <div class="modal-header-container">
                <!-- <span class="close" id="closeModal">&times;</span> -->
                <h3>Add New Item</h3>
            </div>

            <div class="AdminItemList__modal-form-container">
                <div class="AdminItemList__modal-left">
                    <img id="item-preview" src="../../assets/images/image-preview.jpg" alt="image item-image" class="item-image-icon">
                    <input type="file" name="item-image" id="item-image" accept="image/*" style="display: none;">
                    <button type="button" class="AdminItemList__modal-select-image-btn" onclick="document.getElementById('item-image').click();">
                        <img src="../../assets/images/upload-icon.svg" alt="upload icon">
                        Upload photo
                    </button>
                    <span class="error-image-message"></span>
                </div>

                <div class="AdminItemList__modal-right">
                    <input type="hidden" name="action" value="adding-data">
                    <div class="AdminItemList__modal-form-group">
                        <label for="item-name">
                            ITEM NAME :
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="item-name" name="item-name" autocomplete="off" required>
                    </div>
                    <div class="AdminItemList__modal-form-group">
                        <label for="item-category">
                            ITEM CATEGORY :
                            <span class="required">*</span>
                        </label>
                        <!-- <input type="text" id="item-category" name="item-category" autocomplete="off" required> -->
                        <input type="hidden" id="item-category" name="item-category" >
                        <div class="custom-select-container">
                            <div class="custom-select-trigger" id="category-trigger">
                                Select category
                                <img src="../../assets/images/dropdown-icon.svg" alt="dropdown icon">
                            </div>
                            <div class="custom-options">
                                <div class="custom-option" data-value="Hot Beverage">Hot Beverage</div>
                                <div class="custom-option" data-value="Cold Beverage">Cold Beverage</div>
                                <div class="custom-option" data-value="Rice Meal">Rice Meal</div>
                                <div class="custom-option" data-value="Snack">Snack</div>
                                <div class="custom-option" data-value="Dessert">Dessert</div>
                            </div>
                        </div>
                    </div>
                    <div class="AdminItemList__modal-items-group">
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Size : </p>
                            <input type="text" id="item-size" name="item-size1" autocomplete="off" >
                            <input type="text" id="item-size" name="item-size2" autocomplete="off" >
                            <span class="AdminItemList__optional-message">(Optional)</span>
                        </div>
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Price :  <span class="required">*</span></p>
                            <input type="text" id="item-price" name="item-price1" autocomplete="off" required>
                            <input type="text" id="item-price1" name="item-price2" autocomplete="off" >
                            <span class="AdminItemList__optional-message">(Optional)</span>
                        </div>
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Stock :  <span class="required">*</span></p>
                            <input type="text" id="item-stock" name="item-stock1" autocomplete="off" required>
                            <input type="text" id="item-stock1" name="item-stock2" autocomplete="off" >
                            <span class="AdminItemList__optional-message">(Optional)</span>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer-container">
                <button type="button" class="AdminItemList__modal-cancel-button">
                    Cancel
                </button>
                <button type="submit" class="AdminItemList__modal-save-button">
                    Add Item
                </button>
            </div>

        </form>
    </div>



    <!-- Edit Modal structure -->
    <div class="modal" id="itemEditModal">
        <form class="modal-content" action="" method="POST" enctype="multipart/form-data">
            <div class="modal-header-container">
                <!-- <span class="close" id="closeModal">&times;</span> -->
                <h3>Edit Item</h3>
            </div>

            <div class="AdminItemList__modal-form-container">
                <div class="AdminItemList__modal-left">
                    <img id="item-edit-preview" src="../../assets/images/image-preview.jpg" alt="image item-image" class="item-image-icon">
                    <input type="file" name="item-edit-image" id="item-edit-image" accept="image/*" style="display: none;">
                    <button type="button" class="AdminItemList__modal-select-image-btn" onclick="document.getElementById('item-edit-image').click();">
                        <img src="../../assets/images/upload-icon.svg" alt="upload icon">
                        Upload photo
                    </button>
                    <span class="error-image-message"></span>
                </div>

                <div class="AdminItemList__modal-right">
                    <input type="hidden" name="action" value="editing-data">
                    <input type="hidden" name="product-id" id="edit-product-id" value="">
                    <input type="hidden" name="current-image" id="current-image" value="">
                    <div class="AdminItemList__modal-form-group">
                        <label for="item-edit-name">
                            ITEM NAME :
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="item-edit-name" name="item-edit-name" autocomplete="off" required>
                    </div>
                    <div class="AdminItemList__modal-form-group">
                        <label for="item-edit-category">
                            ITEM CATEGORY :
                            <span class="required">*</span>
                        </label>
                        <input type="hidden" id="item-edit-category" name="item-edit-category" autocomplete="off" required>
                        <div class="custom-select-container" id="custom-edit-container">
                            <div class="custom-select-trigger" id="category-edit-trigger">
                                Select category
                                <img src="../../assets/images/dropdown-icon.svg" alt="dropdown icon">
                            </div>
                            <div class="custom-options">
                                <div class="custom-edit-option" data-value="Hot Beverage">Hot Beverage</div>
                                <div class="custom-edit-option" data-value="Cold Beverage">Cold Beverage</div>
                                <div class="custom-edit-option" data-value="Rice Meal">Rice Meal</div>
                                <div class="custom-edit-option" data-value="Snack">Snack</div>
                                <div class="custom-edit-option" data-value="Dessert">Dessert</div>
                            </div>
                        </div>
                    </div>
                    <div class="AdminItemList__modal-items-group">
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Size : </p>
                            <input type="text" id="item-edit-size" name="item-edit-size1" autocomplete="off" >
                            <input type="text" id="item-edit-size" name="item-edit-size2" autocomplete="off" >
                            <span class="AdminItemList__optional-message">(Optional)</span>
                        </div>
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Price :  <span class="required">*</span></p>
                            <input type="text" id="item-edit-price" name="item-edit-price1" autocomplete="off" required>
                            <input type="text" id="item-edit-price1" name="item-edit-price2" autocomplete="off" >
                            <span class="AdminItemList__optional-message">(Optional)</span>
                        </div>
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Stock :  <span class="required">*</span></p>
                            <input type="text" id="item-edit-stock" name="item-edit-stock1" autocomplete="off" required>
                            <input type="text" id="item-edit-stock1" name="item-edit-stock2" autocomplete="off" >
                            <span class="AdminItemList__optional-message">(Optional)</span>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer-container">
                <button type="button" class="AdminItemList__modal-cancel-button" id="cancelEditButton">
                    Cancel
                </button>
                <button type="submit" name="edit-submit" class="AdminItemList__modal-save-button" id="saveEditButton">
                    Save
                </button>
            </div>

        </form>
    </div>


        <!--Delete Item Modal structure -->
        <div class="modal" id="deleteItemModal">
            <div class="Modal_fade-in AdminItemList__modal-delete-content">
                <div class="AdminItemList__modal-delete-header-container">
                    <h3>Delete Item</h3>
                </div>

                <p class="AdminItemList__modal-delete-first-p">Are you sure you want to delete this item?</p>
                <p class="AdminItemList__modal-delete-second-p">This action cannot be undone.</p>
                <p class="AdminItemList__modal-span-item-name">Item Name:  <span id="deleteItemName"></span></p>
    
                <!-- <div class="AdminItemList__modal-delete-item-name">
                    <span class="AdminItemList__modal-span-item-name">Item Name: </span>
                    <div class="AdminItemList__modal-item-name-div">
                        <span id="deleteItemName"></span>
                    </div>
                </div> -->

                <form action="" method="post" id="deleteItemForm">
                    <input type="hidden" name="delete_product_id" id="deleteProductId">
                    <input type="hidden" name="action" value="delete">

                    <div class="AdminItemList__modal-delete-button-inner-group">
                        <button type="button" class="AdminItemList__modal-delete-cancel-pass-button">
                            Cancel
                        </button>
                        <button type="submit" class="AdminItemList__modal-delete-confirm-pass-button">
                            Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="logoutSideBarModal">
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

    <script src="../../assets/js/admin/admin_item_lister.js"></script>
</body>

</html>