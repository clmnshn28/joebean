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

?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cashier Dashboard | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/cashier/cashier_dashboard.css">
        <link rel="stylesheet" href="../../assets/css/modals.css">
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
                                <button class="CashierDashboard__category-button selected">
                                    <img class="CashierDashboard__category-button-img" src="../../assets/images/selected-cup-coffee.png" alt=" cup coffee">
                                </button>
                                <button class="CashierDashboard__category-button">
                                    <img class="CashierDashboard__category-button-img" src="../../assets/images/unselected-coffee.png" alt="hot coffee">
                                </button>
                                <button class="CashierDashboard__category-button">
                                    <img class="CashierDashboard__category-button-imgs" src="../../assets/images/unselected-shake.png" alt="shake">
                                </button>
                                <button class="CashierDashboard__category-button">
                                    <img class="CashierDashboard__category-button-img" src="../../assets/images/unselected-meal.png" alt="meal">
                                </button>
                                <button class="CashierDashboard__category-button">
                                    <img class="CashierDashboard__category-button-img" src="../../assets/images/unselected-pasta.png" alt="pasta">
                                </button>
                                <button class="CashierDashboard__category-button">
                                    <img class="CashierDashboard__category-button-img" src="../../assets/images/unselected-pasta.png" alt="pasta">
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="CashierDashboard__menu-items-section">
                        <div class="CashierDashboard__menu-items-wrapper">
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
                </div>
            </div>
            <div class="CashierDashboard__order-section">
                <div class="CashierDashboard__cashier-details-container">
                    <img src="../../assets/images/avatars/default.jpg" alt="">
                    <div class="CashierDashboard__cashier-name">
                        <span>Celmin Shane Quizon</span>
                        <span>Cashier</span>
                    </div>
                    <button class="CashierDashboard__modal-dots-btn">•••</button>
                </div>
                <div class="CashierDashboard__order-list-container">
                    <div class="CashierDashboard__order-list-wrapper">
                        
                        <div class="CashierDashboard__order-selected-container">
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
                        </div>
                        <div class="CashierDashboard__order-selected-container">
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
                        </div>

                    </div>
                </div>
                <div class="CashierDashboard__place-order-container">
                    <div class="CashierDashboard__total-container">
                        <span>Total</span>
                        <span>:</span>
                        <span>₱ 89.00</span>
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
                        <img id="modalImage" src="../../assets/images/image-preview.jpg" alt="image item-image" class="CashierDashboard__item-image-icon">
                    </div>

                    <div class="CashierDashboard__modal-right">
                        <p class="CashierDashboard__fullname" id="modalFullname"> Celmin Shane Arceo Quizon</p>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Cashier ID <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalUsername">1</p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Username <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalUsername">clmnshn28</p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Gender <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalGender">Male</p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Age <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalAge">22</p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Birthdate <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalBirthdate">December 28, 2002</p>
                        </div>
                        <div class="CashierDashboard__details-list">
                            <p class="CashierDashboard__detail-name">
                                Account Created <span>:</span>
                            </p>
                            <p class="CashierDashboard__detail-value" id="modalCreatedAt">April 04, 2002 - 04:53 PM</p>
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
        <div class="modal" id="profileDetailsModal">
            <div class="CashierDashboard__place-order-modal-content">

                <!-- Left Section - Order Details -->
                <div class="CashierDashboard__order-section-modal">
                    <h2 class="CashierDashboard__section-title">TOTAL ITEMS</h2>
                    <div class="CashierDashboard__final-order-content">
                        <div class="CashierDashboard__item-title-container">
                            <div class="CashierDashboard__item-qty-title">QTY.</div>
                            <div class="CashierDashboard__item-product-title">Product</div>
                            <div class="CashierDashboard__item-price-title">Price</div>
                        </div>
                        <div class="CashierDashboard__order-for-content">
                            <div class="CashierDashboard__order-wrapper">
                                <div class="CashierDashboard__order-items">
                                    <div class="CashierDashboard__item-qty-value">1</div>
                                    <div class="CashierDashboard__item-product-value-content">
                                        <p>Spanish Latte</p>
                                        <div class="CashierDashboard__item-product-sub-content">
                                            <span>Venti</span>
                                        </div>
                                    </div>
                                    <div class="CashierDashboard__item-price-value">₱89.00</div>
                                </div>
                                <div class="CashierDashboard__order-items">
                                    <div class="CashierDashboard__item-qty-value">1</div>
                                    <div class="CashierDashboard__item-product-value-content">
                                        <p>Spanish Latte</p>
                                        <div class="CashierDashboard__item-product-sub-content">
                                            <span>Venti</span>
                                        </div>
                                    </div>
                                    <div class="CashierDashboard__item-price-value">₱89.00</div>
                                </div>
        
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
                            <button class="CashierDashboard__numpad-btn">1</button>
                            <button class="CashierDashboard__numpad-btn">2</button>
                            <button class="CashierDashboard__numpad-btn">3</button>
                            <button class="CashierDashboard__numpad-btn function-btn">+10</button>
                            <button class="CashierDashboard__numpad-btn">4</button>
                            <button class="CashierDashboard__numpad-btn">5</button>
                            <button class="CashierDashboard__numpad-btn">6</button>
                            <button class="CashierDashboard__numpad-btn function-btn">+20</button>
                            <button class="CashierDashboard__numpad-btn">7</button>
                            <button class="CashierDashboard__numpad-btn">8</button>
                            <button class="CashierDashboard__numpad-btn">9</button>
                            <button class="CashierDashboard__numpad-btn function-btn">+50</button>
                            <button class="CashierDashboard__numpad-btn function-btn">+/-</button>
                            <button class="CashierDashboard__numpad-btn">0</button>
                            <button class="CashierDashboard__numpad-btn">.</button>
                            <button class="CashierDashboard__numpad-btn function-btn">⌫</button>
                        </div>
                        
                        <div class="CashierDashboard__action-buttons">
                            <button class="btn CashierDashboard__btn-cash">
                                CASH
                                <div class="CashierDashboard__check-payment">
                                    <img src="../../assets/images/check-icon.svg" alt="check icon">
                                </div>
                            </button>
                            <div class="CashierDashboard__done-cancel-button">
                                <button class="btn CashierDashboard__btn-done">DONE</button>
                                <button class="btn CashierDashboard__btn-cancel">CANCEL</button>
                            </div>
                        </div>
                        
                        <div class="CashierDashboard__payment-method">
                            <button class="btn CashierDashboard__btn-gcash">
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

            </div>
        </div>

        <script src="../../assets/js/cashier/cashier_dashboard.js"></script>
    </body>
</html>