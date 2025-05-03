
document.querySelector('.AdminLogin__type-button').addEventListener('click', function () {
    window.location.href = 'cashier_login.php';
});

// password Toggle
const passwordInput = document.querySelector('input[type="password"]');
const eyeIcon = document.querySelector(".AdminLogin__eye-icon");

let isPasswordVisible = false;

eyeIcon.addEventListener("click",  () => {
    isPasswordVisible = !isPasswordVisible;

    if (isPasswordVisible) {
        // Show password
        passwordInput.type = "text";
        eyeIcon.src = "../../assets/images/eye-open-icon.svg"; 
    } else {
        // Hide password
        passwordInput.type = "password";
        eyeIcon.src = "../../assets/images/eye-close-icon.svg";
    }
});


// ==========================================================================
document.querySelector('.AdminLogin__modal-close').addEventListener('click', function () {
    document.getElementById('forgotPasswordModal').style.display = 'none';
    resetForms();
});

let otpTimerInterval;
let remainingTime = 300; 

document.querySelector('.AdminLogin__forgot-password').addEventListener('click', function (e) {
    e.preventDefault();

    const modal = document.getElementById('forgotPasswordModal');
    modal.style.display = 'flex';

    // Reset and show only step 1
    showStep(1);
    resetForms();
});



// Function to show a specific step
function showStep(stepNumber) {
    const steps = document.querySelectorAll('.reset-step');
    steps.forEach(step => step.style.display = 'none');
    
    const modalCloseBtn = document.querySelector('.AdminLogin__modal-close');
   
    if (stepNumber === 'success') {
        document.getElementById('successStep').style.display = 'flex';

        if (modalCloseBtn) {
            modalCloseBtn.style.display = 'none';
        }
    } else {
        document.getElementById('step' + stepNumber).style.display = 'block';

        if (modalCloseBtn) {
            modalCloseBtn.style.display = 'block';
        }
    }
}

// Function to reset all forms
function resetForms() {
    document.getElementById('requestOtpForm').reset();
    document.getElementById('verifyOtpForm').reset();
    document.getElementById('changePasswordForm').reset();
    
    // Clear error messages
    document.querySelectorAll('.AdminLogin__modal-error-message, .AdminLogin__modal-error-otp-message').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
    
    // Reset password requirements
    document.querySelectorAll('.CashierRegister__password-requirements li span').forEach(span => {
        span.className = 'wrong';
        span.innerHTML = '&#10005;';
    });
    
    // Clear OTP inputs
    document.querySelectorAll('.AdminLogin__modal-otp-input').forEach(input => {
        input.value = '';
    });
    
    clearInterval(otpTimerInterval);
}


// ==================== Step 1: Request OTP ====================

document.getElementById('requestOtpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('reset_email').value;
    const errorMessage = this.querySelector('.AdminLogin__modal-error-message');
    const submitButton = this.querySelector('.AdminLogin__modal-send-otp-button');
    
    // Clear previous error
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    
    // Disable the button and change text
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';

    // Send AJAX request to request OTP
    fetch('/joebean/pages/auth/forgot_password_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=request_otp&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show step 2
            showStep(2);
            
            // Display masked email
            document.getElementById('emailDisplay').textContent = data.masked_email;
            
            // Start OTP timer
            startOtpTimer();

            // Reset button state (for when user goes back)
            submitButton.disabled = false;
            submitButton.textContent = 'Send OTP';
        } else {
            // Show error message
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';

                      
            // Re-enable the button
            submitButton.disabled = false;
            submitButton.textContent = 'Send OTP';
        }
    })
    .catch(error => {
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.style.display = 'block';
        console.error('Error:', error);

        // Re-enable the button
        submitButton.disabled = false;
        submitButton.textContent = 'Send OTP';
    });
});

// ==================== Step 2: Verify OTP ====================

// Handle OTP input fields
const otpInputs = document.querySelectorAll('.AdminLogin__modal-otp-input');
const fullOtpInput = document.getElementById('full_otp');

otpInputs.forEach((input, index) => {
    // Move to next input after entering a digit
    input.addEventListener('input', function() {
        if (this.value.length === 1) {
            if (index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        }
        
        // Update hidden full OTP input
        updateFullOtp();
    });
    
    // Handle backspace
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
});

// Update the hidden full OTP input
function updateFullOtp() {
    let otp = '';
    otpInputs.forEach(input => {
        otp += input.value;
    });
    fullOtpInput.value = otp;
}

// Start OTP timer
function startOtpTimer() {
    remainingTime = 300; // 5 minutes
    clearInterval(otpTimerInterval);
    
    // Update timer display
    updateTimerDisplay();
    
    // Start countdown
    otpTimerInterval = setInterval(function() {
        remainingTime--;
        updateTimerDisplay();
        
        if (remainingTime <= 0) {
            clearInterval(otpTimerInterval);
            document.getElementById('resendOtp').style.display = 'inline-block';
        }
    }, 1000);
}

// Update timer display
function updateTimerDisplay() {
    const minutes = Math.floor(remainingTime / 60);
    const seconds = remainingTime % 60;
    const timerDisplay = document.getElementById('otpTimer');
    const resendButton = document.getElementById('resendOtp');

    // Format time as MM:SS
    timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (remainingTime <= 0) {
        // Hide timer text and show resend button when time is up
        document.querySelector('.AdminLogin__modal-otp-timer').innerHTML = 'OTP expired: <button type="button" id="resendOtp" class="AdminLogin__resend-button">Resend OTP</button>';
        
        // Re-attach event listener to the newly created button
        document.getElementById('resendOtp').addEventListener('click', handleResendOtp);
    } else {
        // Show timer and hide resend button while countdown is active
        timerDisplay.style.display = 'inline';
        if (resendButton) {
            resendButton.style.display = 'none';
        }
    }
}



// Handle OTP verification form submission
document.getElementById('verifyOtpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const otp = fullOtpInput.value;
    const errorMessage = this.querySelector('.AdminLogin__modal-error-otp-message');
    const submitButton = this.querySelector('.AdminLogin__modal-send-otp-button');
    
    // Clear previous error
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    
    // Validate OTP length
    if (otp.length !== 6) {
        errorMessage.textContent = 'Please enter a valid 6-digit OTP';
        errorMessage.style.display = 'block';
        return;
    }

    // Disable the button and change text
    submitButton.disabled = true;
    submitButton.textContent = 'Verifying...';
    
    
    // Send AJAX request to verify OTP
    fetch('/joebean/pages/auth/forgot_password_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=verify_otp&otp=${encodeURIComponent(otp)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Clear timer
            clearInterval(otpTimerInterval);
            
            // Show step 3
            showStep(3);

            // Reset button state (for when user goes back)
            submitButton.disabled = false;
            submitButton.textContent = 'Verify OTP';
        } else {
            // Show error message
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';

            // Re-enable the button
            submitButton.disabled = false;
            submitButton.textContent = 'Verify OTP';
        }
    })
    .catch(error => {
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.style.display = 'block';
        console.error('Error:', error);
   
        // Re-enable the button
        submitButton.disabled = false;
        submitButton.textContent = 'Verify OTP';
    });
});

// Handle resend OTP button
function handleResendOtp() {
    // Disable the button
    this.disabled = true;
    this.textContent = 'Resending...';

    // Send AJAX request to resend OTP
    fetch('/joebean/pages/auth/forgot_password_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=resend_otp'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Reset OTP inputs
            otpInputs.forEach(input => {
                input.value = '';
            });
            fullOtpInput.value = '';
            
            // Restore the timer container with both elements
            const timerContainer = document.querySelector('.AdminLogin__modal-otp-timer');
            timerContainer.innerHTML = `OTP expires in: <span id="otpTimer">05:00</span><button type="button" id="resendOtp" class="AdminLogin__resend-button">Resend OTP</button>`;
            
            // Hide the resend button initially
            document.getElementById('resendOtp').style.display = 'none';
            
            // Re-attach event listener to the new resend button
            document.getElementById('resendOtp').addEventListener('click', handleResendOtp);
            
            // Restart timer
            startOtpTimer();

        } else {
            const errorMessage = document.querySelector('.AdminLogin__modal-error-otp-message');
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';

            // Re-enable the button
            this.disabled = false;
            this.textContent = 'Resend OTP';
        }
    })
    .catch(error => {
        console.error('Error:', error);

        // Re-enable the button
        this.disabled = false;
        this.textContent = 'Resend OTP';
        
        // Show error message
        const errorMessage = document.querySelector('.AdminLogin__modal-error-otp-message');
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.style.display = 'block';
    });
}


// ==================== Step 3: Reset Password ====================

// Password validation
const newPasswordInput = document.getElementById('new_password');
const confirmPasswordInput = document.getElementById('confirm_password');

// Password requirements elements
const lengthRequirement = document.getElementById('lengthRequirement');
const caseRequirement = document.getElementById('caseRequirement');
const specialRequirement = document.getElementById('specialRequirement');

// Check password requirements as user types
newPasswordInput.addEventListener('input', function() {
    const password = this.value;
    
    // Check length (8-100 characters)
    if (password.length >= 8 && password.length <= 100) {
        lengthRequirement.querySelector('span').className = 'check';
        lengthRequirement.querySelector('span').innerHTML = '&#10003;';
    } else {
        lengthRequirement.querySelector('span').className = 'wrong';
        lengthRequirement.querySelector('span').innerHTML = '&#10005;';
    }
    
    // Check case (uppercase and lowercase)
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
        caseRequirement.querySelector('span').className = 'check';
        caseRequirement.querySelector('span').innerHTML = '&#10003;';
    } else {
        caseRequirement.querySelector('span').className = 'wrong';
        caseRequirement.querySelector('span').innerHTML = '&#10005;';
    }
    
    // Check special character or number
    if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) {
        specialRequirement.querySelector('span').className = 'check';
        specialRequirement.querySelector('span').innerHTML = '&#10003;';
    } else {
        specialRequirement.querySelector('span').className = 'wrong';
        specialRequirement.querySelector('span').innerHTML = '&#10005;';
    }
});

// Handle password reset form submission
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const errorMessage = this.querySelector('.AdminLogin__modal-error-otp-message');
    const submitButton = this.querySelector('.AdminLogin__reset-pass-button');
    
    // Clear previous error
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        errorMessage.textContent = 'Passwords do not match';
        errorMessage.style.display = 'block';
        return;
    }
    
    // Validate password meets requirements
    if (lengthRequirement.querySelector('span').className !== 'check' ||
        caseRequirement.querySelector('span').className !== 'check' ||
        specialRequirement.querySelector('span').className !== 'check') {
        errorMessage.textContent = 'Password does not meet all requirements';
        errorMessage.style.display = 'block';
        return;
    }
    
    // Disable the button and change text
    submitButton.disabled = true;
    submitButton.textContent = 'Resetting...';

    // Send AJAX request to reset password
    fetch('/joebean/pages/auth/forgot_password_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reset_password&new_password=${encodeURIComponent(newPassword)}&confirm_password=${encodeURIComponent(confirmPassword)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show success message
            showStep('success');

            // Reset button state
            submitButton.disabled = false;
            submitButton.textContent = 'Reset Password';

        } else {
            // Show error message
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';

            // Re-enable the button
            submitButton.disabled = false;
            submitButton.textContent = 'Reset Password';
        }
    })
    .catch(error => {
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.style.display = 'block';
        console.error('Error:', error);

        // Re-enable the button
        submitButton.disabled = false;
        submitButton.textContent = 'Reset Password';
    });
});


// Handle back button in step 2
document.querySelector('#backButton2').addEventListener('click', function() {
    showStep(1);
});

// Handle back button in step 3
document.querySelector('#backButton3').addEventListener('click', function() {
    showStep(2);
});

// Handle return to login button
document.getElementById('returnToLogin').addEventListener('click', function() {
    document.getElementById('forgotPasswordModal').style.display = 'none';
    resetForms();
});