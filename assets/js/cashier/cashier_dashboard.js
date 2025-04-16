
const logoutSideBarModal = document.getElementById("logoutSideBarModal");

document.querySelector('.Logout__modal-cancel-button').addEventListener('click', function() {
    logoutSideBarModal.style.display = "none";
});

document.querySelector('#logoutBtnModal').addEventListener('click', function() {
    logoutSideBarModal.style.display = "flex";
});


// ==========================================================================================


const profileModal = document.getElementById('profileDetailsModal');
const dotsBtn = document.querySelector('.CashierDashboard__modal-dots-btn');
const closeBtn = document.getElementById('closeModal');

dotsBtn.addEventListener('click', function() {
    profileModal.style.display = 'flex';
});

closeBtn.addEventListener('click', function() {
    profileModal.style.display = 'none';
    // profileModal.querySelector('.CashierDashboard__relative-modal').classList.add('modal-fadeout');
        
    // // Wait for animation to complete before redirecting
    // setTimeout(function() {
    //     profileModal.style.display = 'none';
    //     profileModal.querySelector('.CashierDashboard__relative-modal').classList.remove('modal-fadeout');
    // }, 300); 
});

// ==========================================================================================


  // Get all category buttons
  const categoryButtons = document.querySelectorAll('.CashierDashboard__category-button');
        
  categoryButtons.forEach(button => {
      button.addEventListener('click', function() {

          // Get category from data attribute
          const category = this.getAttribute('data-category');
          // Redirect to load the selected category
          window.location.href = `cashier_dashboard.php?category=${category}`;
      });
  });


// ==========================================================================================
// Function to save cart to localStorage
function saveCartToLocalStorage() {
    localStorage.setItem('joebean_cart', JSON.stringify(cart));
}

// Function to load cart from localStorage
function loadCartFromLocalStorage() {
    const savedCart = localStorage.getItem('joebean_cart');
    if (savedCart) {
        return JSON.parse(savedCart);
    }
    return [];
}


// Initialize shopping cart and order display elements
let cart = loadCartFromLocalStorage();
const orderListWrapper = document.querySelector('.CashierDashboard__order-list-wrapper');
const totalDisplay = document.querySelector('.CashierDashboard__total-container span:last-child');

const sizeButtons = document.querySelectorAll('.CashierDashboard__size-btn');
    
sizeButtons.forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        const productSize = this.dataset.productSize;
        const productPrice = parseFloat(this.dataset.productPrice);
        const subcategory = this.dataset.subcategory; 
        const stockElement = this.closest('.CashierDashboard__product-size-container')
            .querySelector('.CashierDashboard__product-stock span');
        let currentStock = parseInt(stockElement.textContent);
        
        // Check if there's stock available
        if (currentStock > 0) {
            // Decrease stock by 1
            currentStock--;
            stockElement.textContent = currentStock;
            
            // Add item to cart
            addToCart(productId, productName, productSize, productPrice, subcategory);
            
            // Update order display
            updateOrderDisplay();
            
            // Update total
            updateTotal();
        } else {
            // Show error message for out of stock
            const errorModal = document.getElementById('ErrorPaymentModal');
            const errorModalText = document.querySelector('.ErrorPayment__modal-p');
            const errorModalHeader = document.querySelector('.ErrorPayment__modal-content-header-container h3');
            const icons = document.querySelectorAll('.ErrorPayment__modal-content-header-container img');
            const errorIcon = icons[0];   
            const successIcon = icons[1];
            
            errorModal.style.display = 'flex';
            errorModalHeader.style.color = '#a53f3f';
            errorModalHeader.textContent = 'Out of Stock';
            errorModalText.textContent = 'This item is currently out of stock.';
            errorIcon.style.display = 'block';
            successIcon.style.display = 'none';
        }
    });
});


// Function to add item to cart
function addToCart(productId, productName, productSize, productPrice, subCategory) {
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(item => 
        item.id === productId && item.size === productSize
    );
    
    if (existingItemIndex !== -1) {
        // Increment quantity if item exists
        cart[existingItemIndex].quantity++;
    } else {
        // Add new item to cart if it doesn't exist
        cart.push({
            id: productId,
            name: productName,
            size: productSize,
            price: productPrice,
            quantity: 1,
            subcategory: subCategory
        });
    }

       // Save cart to localStorage
       saveCartToLocalStorage();
}


orderListWrapper.innerHTML = `
    <div class="CashierDashboard__empty-cart-message">
        <p>No items in cart</p>
    </div>`;
updateOrderDisplay();
updateTotal();
updateStockDisplay();

// Update stock display based on items in cart
function updateStockDisplay() {
    // Get all size buttons
    const sizeButtons = document.querySelectorAll('.CashierDashboard__size-btn');
    
    // For each item in cart
    cart.forEach(item => {
        // Find corresponding size buttons
        sizeButtons.forEach(button => {
            if (button.dataset.productId === item.id && button.dataset.productSize === item.size) {
                // Find stock element
                const stockElement = button.closest('.CashierDashboard__product-size-container')
                    .querySelector('.CashierDashboard__product-stock span');
                
                // Get displayed stock value
                const displayedStock = parseInt(stockElement.textContent);
                
                // Calculate actual stock by subtracting item quantity
                // This assumes the page loads with the database's current stock value
                if (!stockElement.hasAttribute('data-adjusted')) {
                    stockElement.textContent = displayedStock - item.quantity;
                    stockElement.setAttribute('data-adjusted', 'true');
                }
            }
        });
    });
}

// Function to update order display
function updateOrderDisplay() {
    // Clear current order list
    orderListWrapper.innerHTML = '';
    
    if (cart.length === 0) {
        orderListWrapper.innerHTML = `
            <div class="CashierDashboard__empty-cart-message">
                <p>No items in cart</p>
            </div>`;
        return;
    }
    // Add each item to order list
    cart.forEach((item, index) => {
        const orderItem = document.createElement('div');
        orderItem.className = 'CashierDashboard__order-selected-container';
        orderItem.innerHTML = `
            <div class="CashierDashboard__order-item-circle-container">
                <div class="CashierDashboard__order-circle-number">
                    <span>${item.quantity}</span>
                </div>
            </div>
            <div class="CashierDashboard__order-item-name-container">
                <span>${item.name}</span>
                <div class="CashierDashboard__order-item-size-price-container">
                    <p>${item.size ? item.size : item.subcategory }</p>/<p>₱${item.price.toFixed(2)}</p>
                </div>
            </div>
            <div class="CashierDashboard__order-item-price-delete-container">
               
         
                <div class="CashierDashboard__order-delete-icon-container" data-index="${index}">
                    <img class="CashierDashboard__order-delete-icon" src="../../assets/images/trashcan-icon.svg" alt="trashcan icon">
                </div>    
            </div>
        `;
        orderListWrapper.appendChild(orderItem);
    });
    
    // Add event listeners to delete buttons
    document.querySelectorAll('.CashierDashboard__order-delete-icon-container').forEach(button => {
        button.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            removeFromCart(index);
        });
    });
}

// Function to remove item from cart
function removeFromCart(index) {
    if (index >= 0 && index < cart.length) {
        const item = cart[index];
        
        // Find the stock element for this product
        const sizeButtons = document.querySelectorAll('.CashierDashboard__size-btn');
        let stockElement = null;
        
        sizeButtons.forEach(button => {
            if (button.dataset.productId === item.id && button.dataset.productSize === item.size) {
                stockElement = button.closest('.CashierDashboard__product-size-container')
                    .querySelector('.CashierDashboard__product-stock span');
            }
        });
        
        if (stockElement) {
            // Increase stock by the quantity removed
            let currentStock = parseInt(stockElement.textContent);
            currentStock += item.quantity;
            stockElement.textContent = currentStock;
        }
        
        // Remove item from cart
        cart.splice(index, 1);
        
        // Update order display
        updateOrderDisplay();
        
        // Update total
        updateTotal();

        // Save cart to localStorage
        saveCartToLocalStorage();
    }
}

// Function to update total
function updateTotal() {
    let total = 0;
    cart.forEach(item => {
        total += item.price * item.quantity;
    });
    totalDisplay.textContent = `₱ ${total.toFixed(2)}`;
    
    // Update the total in the modal as well
    const totalValueElement = document.querySelector('.CashierDashboard__payment-row:nth-child(1) .CashierDashboard__payment-value');
    if (totalValueElement) {
        totalValueElement.textContent = total.toFixed(2);
    }
}

// ==========================================================================================


const placeOrderModal = document.getElementById('placeOrderModal');
const placeOrderBtn = document.querySelector('.CashierDashboard__place-order-btn');
const cancelBtn = document.querySelector('.CashierDashboard__btn-cancel');
const doneBtn = document.querySelector('.CashierDashboard__btn-done');

// Payment method selection elements
const cashBtn = document.querySelector('.CashierDashboard__btn-cash');
const gcashBtn = document.querySelector('.CashierDashboard__btn-gcash');
const refInput = document.querySelector('.CashierDashboard__ref-input-group input');

// numpad
const paymentInput = document.querySelector('.CashierDashboard__payment-input');
const numpadButtons = document.querySelectorAll('.CashierDashboard__numpad-btn');
const paymentValueElement = document.querySelector('.CashierDashboard__payment-row:nth-child(2) .CashierDashboard__payment-value');
const totalValueElement = document.querySelector('.CashierDashboard__payment-row:nth-child(1) .CashierDashboard__payment-value');
const changeValueElement = document.querySelector('.CashierDashboard__payment-rows .CashierDashboard__payment-value');


placeOrderBtn.addEventListener('click', function() {
        // Update item list in the modal
    const orderForContent = document.querySelector('.CashierDashboard__order-wrapper');
    orderForContent.innerHTML = '';


    if (cart.length === 0) {
        // Show error message for empty cart
        const errorModal = document.getElementById('ErrorPaymentModal');
        const errorModalText = document.querySelector('.ErrorPayment__modal-p');
        const errorModalHeader = document.querySelector('.ErrorPayment__modal-content-header-container h3');
        const icons = document.querySelectorAll('.ErrorPayment__modal-content-header-container img');
        const errorIcon = icons[0];   
        const successIcon = icons[1];
        
        errorModal.style.display = 'flex';
        errorModalHeader.style.color = '#a53f3f';
        errorModalHeader.textContent = 'Empty Cart';
        errorModalText.textContent = 'Please add items to your cart before placing an order.';
        errorIcon.style.display = 'block';
        successIcon.style.display = 'none';
        return;
    }

    cart.forEach(item => {
        const orderItem = document.createElement('div');
        orderItem.className = 'CashierDashboard__order-items';
        orderItem.innerHTML = `
            <div class="CashierDashboard__item-qty-value">${item.quantity}</div>
            <div class="CashierDashboard__item-product-value-content">
                <p>${item.name}</p>
                <div class="CashierDashboard__item-product-sub-content">
                    <span>${item.size ? item.size : item.subcategory }</span>
                </div>
            </div>
            <div class="CashierDashboard__item-price-value">₱${(item.price * item.quantity).toFixed(2)}</div>
        `;
        orderForContent.appendChild(orderItem);
    });
    
    placeOrderModal.style.display = 'flex';

    // Reset input field
    paymentInput.value = '';
    paymentValueElement.textContent = '0.00';
    changeValueElement.textContent = '0.00';

    // Set cash as default payment method
    selectPaymentMethod('cash');
});

cancelBtn.addEventListener('click', function() {
    placeOrderModal.style.display = 'none';
    resetPaymentSelections();
    changeValueElement.style.color = '';
});



// Payment method selection
function selectPaymentMethod(method) {
    // Hide all checkmarks first
    document.querySelectorAll('.CashierDashboard__check-payment').forEach(check => {
        check.style.display = 'none';
    });
    
    if (method === 'cash') {
        cashBtn.querySelector('.CashierDashboard__check-payment').style.display = 'flex';
        refInput.removeAttribute('required');
        refInput.setAttribute('disabled', 'disabled');
        refInput.value = '';
        
    } else if (method === 'gcash') {
        gcashBtn.querySelector('.CashierDashboard__check-payment').style.display = 'flex';
        refInput.setAttribute('required', 'required');
        refInput.removeAttribute('disabled');
        refInput.focus();
    }
}


function resetPaymentSelections() {
    document.querySelectorAll('.CashierDashboard__check-payment').forEach(check => {
        check.style.display = 'none';
    });
    refInput.value = '';
    paymentInput.value = '';
    paymentValueElement.textContent = '0.00';
    changeValueElement.textContent = '0.00';
    changeValueElement.style.color = '';
    refInput.removeAttribute('required');
    refInput.setAttribute('disabled', 'disabled');
}

// Add click event listeners for payment buttons
cashBtn.addEventListener('click', function() {
    selectPaymentMethod('cash');
});

gcashBtn.addEventListener('click', function() {
    selectPaymentMethod('gcash');
});


// Numpad functionality
numpadButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Get the current value in the payment input
        let currentValue = paymentInput.value;
        const key = this.dataset.key;

        if (key === 'backspace') {
            // Backspace button - remove last character
            paymentInput.value = currentValue.slice(0, -1);
        } else if (key === '+/-') {
            // Toggle negative/positive - not typically needed for payment systems
            if (currentValue.startsWith('-')) {
                paymentInput.value = currentValue.substring(1);
            } else if (currentValue !== '') {
                paymentInput.value = '-' + currentValue;
            }
        }else if (key === '+10') {
            // Add 10 to the current value
            const newValue = parseFloat(currentValue || 0) + 10;
            paymentInput.value = newValue.toString();
        }else if (key === '+20') {
            // Add 20 to the current value
            const newValue = parseFloat(currentValue || 0) + 20;
            paymentInput.value = newValue.toString();
        } else if (key === '+50') {
            // Add 50 to the current value
            const newValue = parseFloat(currentValue || 0) + 50;
            paymentInput.value = newValue.toString();
        } else if (key === '.') {
            // Only add decimal point if there isn't one already
            if (!currentValue.includes('.')) {
                paymentInput.value = currentValue + '.';
            }
        } else {
            // Regular number - append to current value
            paymentInput.value = currentValue + key;
        }

        updatePaymentDisplay();
        
    });
});


// Function to update payment display and calculate change
function updatePaymentDisplay() {
    const paymentAmount = parseFloat(paymentInput.value) || 0;
    paymentValueElement.textContent = paymentAmount.toFixed(2);

    const currentTotal = parseFloat(totalValueElement.textContent) || 0;
    
    // Calculate change
    const change = paymentAmount - currentTotal;
    changeValueElement.textContent = (change >= 0 ? change : 0).toFixed(2);

    // Highlight change in green if positive
    if (change >= 0) {
        changeValueElement.style.color = '#4CAF50';
    } else {
        changeValueElement.style.color = '';
    }

}

// Also update when typing directly in the payment input
paymentInput.addEventListener('input', updatePaymentDisplay);


// Validate payment before completing order
doneBtn.addEventListener('click', function(e) {
    e.preventDefault();

    const paymentAmount = parseFloat(paymentInput.value) || 0;
    const totalAmountValue = parseFloat(totalValueElement.textContent) || 0;
    
    const errorModal = document.getElementById('ErrorPaymentModal');
    const errorModalText = document.querySelector('.ErrorPayment__modal-p');
    const errorModalHeader = document.querySelector('.ErrorPayment__modal-content-header-container h3');
    const icons = document.querySelectorAll('.ErrorPayment__modal-content-header-container img');
    const errorIcon = icons[0];   
    const successIcon = icons[1];

    // Check if GCash is selected and reference number is provided
    const isGcash = gcashBtn.querySelector('.CashierDashboard__check-payment').style.display === 'flex';
    const paymentMethod = isGcash ? 'GCash' : 'Cash';
    const referenceNumber = isGcash ? refInput.value.trim() : '';

    if (isGcash) {
        if (!refInput.value.trim()) {
            errorModal.style.display = 'flex';
            errorModalHeader.style.color = '#a53f3f';
            errorModalHeader.textContent = 'Missing Reference Number';
            errorModalText.textContent = 'Please enter a reference number for GCash payment.';
            errorIcon.style.display = 'block';
            successIcon.style.display = 'none';
            return;
        }
    }
    // Check if payment is sufficient
    if (paymentAmount < totalAmountValue) {
        errorModal.style.display = 'flex';
        errorModalHeader.style.color = '#a53f3f';
        errorModalHeader.textContent = 'Insufficient Amount';
        errorModalText.textContent = 'Payment amount is less than the total amount. Please enter a sufficient payment.';
        errorIcon.style.display = 'block';
        successIcon.style.display = 'none';
        return;
    }

    // Calculate change
    const changeAmount = paymentAmount - totalAmountValue;
        
    // Set form field values
    document.getElementById('cartDataField').value = JSON.stringify(cart);
    document.getElementById('paymentAmountField').value = paymentAmount;
    document.getElementById('paymentMethodField').value = paymentMethod;
    document.getElementById('referenceNumberField').value = referenceNumber;
    document.getElementById('totalAmountField').value = totalAmountValue;
    document.getElementById('changeAmountField').value = changeAmount;
    
    // Submit the form with AJAX
    const formData = new FormData(document.getElementById('transactionForm'));
    
    fetch('cashier_dashboard.php?action=donePayment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const cartCopy = [...cart]; 
            const paymentInfo = {
                totalAmount: parseFloat(totalValueElement.textContent) || 0,
                paymentAmount: parseFloat(paymentInput.value) || 0,
                paymentMethod: gcashBtn.querySelector('.CashierDashboard__check-payment').style.display === 'flex' ? 'GCash' : 'Cash',
                referenceNumber: refInput.value.trim(),
                changeAmount: parseFloat(changeValueElement.textContent) || 0
            };

            
            errorModal.style.display = 'flex';
            errorModalHeader.textContent = 'Payment Successful';
            errorModalHeader.style.color = '#4CAF50';
            errorModalText.textContent = data.message;
            errorIcon.style.display = 'none';
            successIcon.style.display = 'block';

            cart = [];
            localStorage.removeItem('joebean_cart');
            updateOrderDisplay();
            updateTotal();
            placeOrderModal.style.display = 'none';
            resetPaymentSelections();

            setTimeout(() => {
                errorModal.style.display = 'none';
                showReceipt(data, cartCopy, paymentInfo);
            }, 1500);
        } else {
            // Show error message
            errorModal.style.display = 'flex';
            errorModalHeader.style.color = '#a53f3f';
            errorModalHeader.textContent = 'Transaction Failed';
            errorModalText.textContent = data.message;
            errorIcon.style.display = 'block';
            successIcon.style.display = 'none';
        }
    })
    .catch(error => {
        // Handle fetch error
        errorModal.style.display = 'flex';
        errorModalHeader.style.color = '#a53f3f';
        errorModalHeader.textContent = 'Transaction Error';
        errorModalText.textContent = 'An error occurred while processing your transaction.';
        errorIcon.style.display = 'block';
        successIcon.style.display = 'none';
        console.error('Error:', error);
    });

});


document.querySelector('.ErrorPayment__modal-cancel-button').addEventListener('click', function() {
    document.getElementById('ErrorPaymentModal').style.display = 'none';
    const errorModalHeader = document.querySelector('.ErrorPayment__modal-content-header-container h3');
    
    // Only focus on inputs if it was an error message
    if (errorModalHeader.textContent !== 'Payment Successful') {
        if (gcashBtn.querySelector('.CashierDashboard__check-payment').style.display === 'flex' && !refInput.value.trim()) {
            refInput.focus();
        } else {
            paymentInput.focus();
        }
    }
});


// ========================================================================
const receiptModal = document.getElementById('receiptModal');
const receiptCloseBtnFooter = document.querySelector('.Receipt__close-btn');
const receiptPrintBtn = document.querySelector('.Receipt__print-btn');

receiptCloseBtnFooter.addEventListener('click', function() {
    receiptModal.style.display = 'none';
});

// Print receipt when clicking Print button
receiptPrintBtn.addEventListener('click', function() {
    window.print();
});



// Function to show receipt after successful transaction
function showReceipt(transactionData, cartItems, paymentInfo) {
    // Set transaction details
    document.getElementById('receiptTransactionId').textContent = transactionData.transaction_id;
    document.getElementById('receiptDate').textContent = formatDateTime(new Date().toLocaleString());
    document.getElementById('receiptCashier').textContent = document.querySelector(".CashierDashboard__cashier-name span:first-child").textContent;

    // Populate items
    const itemsList = document.getElementById('receiptItemsList');
    itemsList.innerHTML = '';

    cartItems.forEach(item => {
        const row = document.createElement('tr');
        const subtotal = item.price * item.quantity;
        
        row.innerHTML = `
            <td>${item.quantity}</td>
            <td>${item.name}</td>
            <td>${item.size ? item.size : "-"}</td>
            <td>₱${item.price.toFixed(2)}</td>
            <td>₱${subtotal.toFixed(2)}</td>
        `;
        
        itemsList.appendChild(row);
    });

    // Set payment details
    document.getElementById('receiptTotal').textContent = `₱${paymentInfo.totalAmount.toFixed(2)}`;
    document.getElementById('receiptPaymentMethod').textContent = paymentInfo.paymentMethod;

    const refRow = document.getElementById('receiptRefRow');
    if (paymentInfo.paymentMethod === 'GCash' && paymentInfo.referenceNumber) {
        document.getElementById('receiptRefNo').textContent = paymentInfo.referenceNumber;
        refRow.style.display = 'flex';
    } else {
        refRow.style.display = 'none';
    }

    document.getElementById('receiptAmountPaid').textContent = `₱${paymentInfo.paymentAmount.toFixed(2)}`;
    document.getElementById('receiptChange').textContent = `₱${paymentInfo.changeAmount.toFixed(2)}`;

    // Show the receipt modal
    receiptModal.style.display = 'flex';


}

function formatDateTime(dateTimeStr) {
    const date = new Date(dateTimeStr);

    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    const month = months[date.getMonth()];
    const day = date.getDate();
    const year = date.getFullYear();

    let hour = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;

    return `${month} ${day}, ${year} — ${hour}:${minutes} ${ampm}`;
}