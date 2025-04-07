
document.querySelector('.CashierLogin__type-button').addEventListener('click', function () {
    window.location.href = 'admin_login.php';
});

// password Toggle
const passwordInput = document.querySelector('input[type="password"]');
const eyeIcon = document.querySelector(".CashierLogin__eye-icon");

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
