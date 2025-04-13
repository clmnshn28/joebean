


const profileModal = document.getElementById('profileDetailsModal');
const dotsBtn = document.querySelector('.CashierDashboard__modal-dots-btn');
const closeBtn = document.getElementById('closeModal');

const logoutBtn = document.getElementById('logoutBtnModal');


dotsBtn.addEventListener('click', function() {
    profileModal.style.display = 'flex';
});

closeBtn.addEventListener('click', function() {
    profileModal.style.display = 'none';
});


logoutBtn.addEventListener('click', function(e) {
    if (confirm('Are you sure you want to log out?')) {
        window.location.href = 'cashier_dashboard.php?action=logout';
    }
});

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

const totalAmount = 178.00;

totalValueElement.textContent = totalAmount.toFixed(2);


placeOrderBtn.addEventListener('click', function() {
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

    // Calculate change
    const change = paymentAmount - totalAmount;
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
doneBtn.addEventListener('click', function() {
    
    const paymentAmount = parseFloat(paymentInput.value) || 0;
    
    // Check if payment is sufficient
    if (paymentAmount < totalAmount) {
        alert('Payment amount is less than the total amount. Please enter a sufficient payment.');
        paymentInput.focus();
        return;
    }
    
    // Check if GCash is selected and reference number is provided
    if (gcashBtn.querySelector('.CashierDashboard__check-payment').style.display === 'flex') {
        if (!refInput.value.trim()) {
            alert('Please enter a reference number for GCash payment.');
            refInput.focus();
            return;
        }
    }
    
    alert('Payment processed successfully!');
    placeOrderModal.style.display = 'none';
    resetPaymentSelections();
});
