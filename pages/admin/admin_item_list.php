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
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $itemName = $_POST["item-name"];
    $itemCategory = $_POST["item-category"];
    $itemPrice1 = $_POST["item-price1"];
    $itemPrice2 = $_POST["item-price2"];
    $itemStock1 = $_POST["item-stock1"];
    $itemStock2 = $_POST["item-stock2"];
    $itemSize1 = $_POST["item-size1"];
    $itemSize2 = $_POST["item-size2"];

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
    <link rel="stylesheet" href="../../assets/css/admin/admin_item_listers.css">
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
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                <?php endif; ?>
                <div class="AdminItemList__header-container">
                    <h3>Item List</h3>
                    <button id="openModalBtn"> + Add Item</button>
                </div>
                <table class="AdminItemList__table-content-item">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Size</th>
                            <th>Item Price</th>
                            <th>Item Category</th>
                            <th>Item Stock</th>
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
                                echo "<td>" . str_replace(",", " <br> ",htmlspecialchars(ucwords(strtolower($row['sizes'])))) . "</td>";
                                echo "<td>₱" . str_replace(",", "<br>₱", htmlspecialchars($row['prices'])) . "</td>";
                                echo "<td>" . htmlspecialchars(ucwords(strtolower($row['item_category']))) . "</td>";
                                echo "<td>" . str_replace(",", " <br> ",htmlspecialchars($row['stocks'])) . "</td>";
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
                        <input type="text" id="item-category" name="item-category" autocomplete="off" required>
                    </div>
                    <div class="AdminItemList__modal-items-group">
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Size :  <span class="required">*</span></p>
                            <input type="text" id="item-size" name="item-size1" autocomplete="off" required>
                            <input type="text" id="item-size" name="item-size2" autocomplete="off" required>
                        </div>
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Price :  <span class="required">*</span></p>
                            <input type="text" id="item-price" name="item-price1" autocomplete="off" required>
                            <input type="text" id="item-price1" name="item-price2" autocomplete="off" required>
                        </div>
                        <div class="AdminItemList__modal-item-price-container">
                            <p>Item Stock :  <span class="required">*</span></p>
                            <input type="text" id="item-stock" name="item-stock1" autocomplete="off" required>
                            <input type="text" id="item-stock1" name="item-stock2" autocomplete="off" required>
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

    <script src="../../assets/js/admin/admin_item_lister.js"></script>
</body>

</html>