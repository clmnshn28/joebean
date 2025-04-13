// password requirements
const passwordInput = document.getElementById("password"); 
const lengthRequirement = document.getElementById("lengthRequirement");
const caseRequirement = document.getElementById("caseRequirement");
const specialRequirement = document.getElementById("specialRequirement");

passwordInput.addEventListener("input", function () {
    const password = passwordInput.value;

    const isLengthValid = password.length >= 8 && password.length <= 100;
    const isCaseValid = /[A-Z]/.test(password) && /[a-z]/.test(password);
    const isSpecialValid = /\d/.test(password) || /[!@#$%^&*(),.?":{}|<>]/.test(password);

    updateRequirement(lengthRequirement, isLengthValid);
    updateRequirement(caseRequirement, isCaseValid);
    updateRequirement(specialRequirement, isSpecialValid);
});

function updateRequirement(element, isValid) {
    const text = element.textContent.trim().slice(1); // remove existing icon sa HTML 
    element.innerHTML = isValid
        ? `<span class="check">&#10004;</span> ${text}`
        : `<span class="wrong">&#10005;</span> ${text}`;
}

// =================================================================================


function allowNumbersOnly(event, maxLength) {
  
    event.target.value = event.target.value.replace(/[^0-9]/g, '');

    if(event.target.value.length > maxLength){
        event.target.value = event.target.value.slice(0, maxLength);
    }
}

// Attach the event listener to each birthday field
document.getElementById("day").addEventListener("input", (e)=> allowNumbersOnly(e, 2));
document.getElementById("month").addEventListener("input", (e)=> allowNumbersOnly(e, 2));
document.getElementById("year").addEventListener("input", (e)=> allowNumbersOnly(e, 4));



// =================================================================================

const avatarInput = document.getElementById('avatar');
const previewImg = document.getElementById('preview');

avatarInput.addEventListener('change', function () {
    const file = avatarInput.files[0];
    if (file) {
        previewImg.src = URL.createObjectURL(file);
    }
});


// =================================================================================


const confirmPasswordInput = document.getElementById("confirmPassword");
const confirmPasswordError = document.querySelector(".error-confirm-message");
const passwordError = document.querySelector("#password + .error-message");


const registerBtn = document.querySelector(".CashierRegister__register-btn");

registerBtn.addEventListener("click", function (event) {
    event.preventDefault(); // Prevent form submission for validation first

    const form = document.querySelector('.CashierRegister__form-container');
    if (!form.checkValidity()) {
        form.reportValidity();  // Triggers the browser's built-in validation UI
        return;
    }
    
    const password = passwordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();
    const avatarFile = avatarInput.files[0];
    const imageError = document.querySelector('.error-image-message');

    // Reset image error message
    imageError.textContent = "";

    // Validation checks
    const isLengthValid = password.length >= 8 && password.length <= 100;
    const isCaseValid = /[A-Z]/.test(password) && /[a-z]/.test(password);
    const isSpecialValid = /\d/.test(password) || /[!@#$%^&*(),.?":{}|<>]/.test(password);

    let isValid = true;

    // Check all password requirements
    if (!isLengthValid || !isCaseValid || !isSpecialValid) {
        passwordError.style.display = "block";
        passwordError.textContent = "Password does not meet the requirements.";
        isValid = false;
    } else {
        passwordError.style.display = "none";
    }

    // Check password === confirm password
    if (password !== confirmPassword) {
        confirmPasswordError.style.display = "block";
        confirmPasswordError.textContent = "Passwords do not match.";
        isValid = false;
    } else {
        confirmPasswordError.style.display = "none";
    }

    
    // Check if image is selected
    const defaultPreview = "../../assets/images/profile-icon.svg";
    const previewSrc = previewImg.getAttribute("src");
    
    if (!avatarFile && previewSrc === defaultPreview) {
        imageError.style.display = "block";
        imageError.textContent = "Image is required.";
        isValid = false;
    } else {
        imageError.style.display = "none";
    }

     // === Birthday Validation ===
     const dayInput = document.getElementById("day");
     const monthInput = document.getElementById("month");
     const yearInput = document.getElementById("year");
     const birthdayError = document.querySelector(".CashierRegister__birthday-group .error-message");
 
     // Validate day, month, and year
     if (dayInput.value.length !== 2 || isNaN(dayInput.value)) {
         birthdayError.style.display = "block";
         birthdayError.textContent = "Please enter a valid day (2 digits).";
         isValid = false;
     }
 
     if (monthInput.value.length !== 2 || isNaN(monthInput.value)) {
         birthdayError.style.display = "block";
         birthdayError.textContent = "Please enter a valid month (2 digits).";
         isValid = false;
     }
 
     if (yearInput.value.length !== 4 || isNaN(yearInput.value)) {
         birthdayError.style.display = "block";
         birthdayError.textContent = "Please enter a valid year (4 digits).";
         isValid = false;
     }

     if (isValid && password === confirmPassword) {
        form.submit(); // Manually submit form if all validations pass
        modal.style.display = 'flex';
    }
    
   
});

