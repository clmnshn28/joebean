<?php
    // Start the session
    session_start();

    include "../../config/db.php";


?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cashier Dashboard | JoeBean</title>
        <link rel="stylesheet" href="../../assets/css/indexs.css">
        <link rel="stylesheet" href="../../assets/css/cashier/cashier_dashboarded.css">
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
    </body>
</html>