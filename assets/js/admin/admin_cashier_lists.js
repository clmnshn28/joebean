document.querySelector('.AdminItemList__logout').addEventListener('click', function(e) {
    if (!confirm('Are you sure you want to log out?')) {
        e.preventDefault();
    }
});


// =================================================================================


// Get search input element
const searchInput = document.querySelector('.AdminItemList__header-search-container input');

searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    filterTable(searchTerm);
});

// Function to filter table rows
function filterTable(searchTerm) {
    const tableBody = document.getElementById('cashierTableBody');
    const tableRows = tableBody.querySelectorAll('tr:not(.no-results-row)');
    let matchFound = false;
    
    // Remove any existing "no results" row
    const existingNoResultsRow = tableBody.querySelector('.no-results-row');
    if (existingNoResultsRow) {
        existingNoResultsRow.remove();
    }
    
    // If search is empty like show all rows
    if (searchTerm === '') {
        tableRows.forEach(row => {
            row.style.display = '';
        });
        return;
    }

    // Loop through all table rows
    tableRows.forEach(row => {
        // Get text content
        const cashierUsername = row.querySelector('td:nth-child(2) ')?.textContent.toLowerCase() || '';
        const cashierFullname = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        
        // Check if row contains the search term
        if (cashierUsername.includes(searchTerm) || cashierFullname.includes(searchTerm)) {
            row.style.display = '';
            matchFound = true;
        } else {
            row.style.display = 'none';
        }
    });

    // Show no-results message if no matches found
    if (!matchFound) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.className = 'no-results-row';
        noResultsRow.innerHTML = `<td colspan="6"><div class="no-data-message" style="text-transform: none;">No items match your search for "${searchTerm}"</div></td>`;
        tableBody.appendChild(noResultsRow);
    }
}

// =================================================================================

    const modal = document.getElementById("itemModal");
    // const viewButton = document.getElementById("viewButton");
    const closeModalButton = document.getElementById("closeModal");

    // Get references to modal content
    const modalImage = document.getElementById('modalImage');
    const modalFullname = document.getElementById('modalFullname');
    const modalUsername = document.getElementById('modalUsername');
    const modalGender = document.getElementById('modalGender');
    const modalAge = document.getElementById('modalAge');
    const modalBirthdate = document.getElementById('modalBirthdate');
    const modalCreatedAt = document.getElementById('modalCreatedAt');

    // When clicking the view button
    document.querySelectorAll('.AdminCashierList__table-data-btn').forEach(button => {
        button.addEventListener('click', function () {
            modalImage.src = '../../assets/images/avatars/' + this.dataset.image;
            modalFullname.textContent = this.dataset.fullname;
            modalUsername.textContent = this.dataset.username;
            modalGender.textContent = this.dataset.gender;
            modalAge.textContent = this.dataset.age;
            modalBirthdate.textContent = this.dataset.birthdate;
            modalCreatedAt.innerHTML = this.dataset.createdAt;

            document.getElementById('resetUserId').value = this.dataset.id;
            document.getElementById('resetUsername').textContent = this.dataset.username || "YOUR_USERNAME";
            
            document.getElementById('deactivateUserId').value = this.dataset.id;


            modal.style.display = 'flex';
            resetModal.style.display = "none";
        });
    });


    // Function to close the modal
    closeModalButton.onclick = function() {
        modal.style.display = "none";
    }


    // ******************************

    const resetModal = document.getElementById("resetPasswordModal");
    const resetPasswordButton = document.querySelector(".AdminCashierList__modal-reset-password-button");
    const cancelPassButton = document.querySelector(".AdminCashierList__modal-cancel-pass-button");

    // Function to open the modal
    if (resetPasswordButton) {
        resetPasswordButton.onclick = function() {
            resetModal.style.display = "flex";
        }
    }

    // Function to close the modal
    cancelPassButton.onclick = function() {
        resetModal.style.display = "none";
        document.querySelector('.AdminCashierList__modal-reset-pass-form-container').reset();
        passwordError.style.display = "none";
    }

    const deactivateModal = document.getElementById("deactivateModal");
    const deactivateButton = document.querySelector(".AdminCashierList__modal-deact-pass-button");
    const confirmDeactivateButton = document.querySelector(".AdminCashierList__modal-deactivate-confirm-pass-button");
    const cancelDeactivateButton = document.querySelector(".AdminCashierList__modal-deactivate-cancel-pass-button");

    deactivateButton.onclick = function() {
        deactivateModal.style.display = "flex";
    }

    cancelDeactivateButton.onclick = function() {
        deactivateModal.style.display = "none";
    }

    confirmDeactivateButton.onclick = function( ) {
        document.getElementById('deactivateForm').submit(); 
    }

    // ******************************

    const reactivateModal = document.getElementById("reactivateModal");
    const reactivateButton = document.querySelector(".AdminCashierList__table-reactivate-data-btn");
    const confirmReactivateButton = document.querySelector(".AdminCashierList__modal-reactivate-confirm-pass-button");
    const cancelReactivateButton = document.querySelector(".AdminCashierList__modal-reactivate-cancel-pass-button");

    // When clicking the reactivate button
    document.querySelectorAll('.AdminCashierList__table-reactivate-data-btn').forEach(button => {
        button.addEventListener('click', function () {
        
            document.getElementById('reactivateUserId').value = this.dataset.id;

            reactivateModal.style.display = 'flex';
        });
    });

    cancelReactivateButton.onclick = function() {
        reactivateModal.style.display = "none";
    }

    confirmReactivateButton.onclick = function() {
        document.getElementById('reactivateForm').submit(); 
    }


    function closeAllModal(){
        resetModal.style.display = "none";
        modal.style.display = "none";
        document.querySelector('.AdminCashierList__modal-reset-pass-form-container').reset();
        passwordError.style.display = "none";
    }

    
    // =================================================================================
    // Pagination functionality
    const leftPaginationBtn = document.querySelector('.AdminItemList__pagination-left');
    const rightPaginationBtn = document.querySelector('.AdminItemList__pagination-right');
    const paginationNumber = document.querySelector('.AdminItemList__pagination-number');

    // Current page from URL or default to 1
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = parseInt(urlParams.get('page')) || 1;

    // Handle left pagination button click
    leftPaginationBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            const newPage = currentPage - 1;
            window.location.href = `admin_cashier_list.php?page=${newPage}`;
        }
    });

    // Handle right pagination button click
    rightPaginationBtn.addEventListener('click', function() {
        const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]);
        if (currentPage < totalPages) {
            const newPage = currentPage + 1;
            window.location.href = `admin_cashier_list.php?page=${newPage}`;
        }
    });

    // Hide pagination if there's only one page
    const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]) || 1;
    if (totalPages <= 1) {
        document.getElementById('paginationContainer').style.display = 'none';
    } else {
        document.getElementById('paginationContainer').style.display = 'flex';
    }



    // =================================================================================
   

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


    const confirmPasswordInput = document.getElementById("confirmPassword");
    const passwordError = document.querySelector(".AdminCashierList__modal-reset-pass-error-message");


    const resetPassBtn = document.querySelector("#fullResetPassword");

    resetPassBtn.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission for validation first
        
        const form = document.querySelector('.AdminCashierList__modal-reset-pass-form-container');
        if (!form.checkValidity()) {
            form.reportValidity();  // Triggers the browser's built-in validation UI
            return;
        }
        
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        
        let isValid = true;

        // Validation checks
        const isLengthValid = password.length >= 8 && password.length <= 100;
        const isCaseValid = /[A-Z]/.test(password) && /[a-z]/.test(password);
        const isSpecialValid = /\d/.test(password) || /[!@#$%^&*(),.?":{}|<>]/.test(password);

        // Check all password requirements
        if (!isLengthValid || !isCaseValid || !isSpecialValid) {
            passwordError.style.display = "flex";
            passwordError.innerHTML = `<img src="../../assets/images/error-icon.svg" alt="error icon"> Password does not meet the requirements.`;
            isValid = false;
        } else {
            passwordError.style.display = "none";

            // Check password === confirm password
            if (password !== confirmPassword) {
                passwordError.style.display = "flex";
                passwordError.innerHTML = `<img src="../../assets/images/error-icon.svg" alt="error icon">Passwords do not match.`;  
                isValid = false;
            } else {
                passwordError.style.display = "none";
            }
        }

    


        if (isValid && password === confirmPassword) {
            form.submit(); // Manually submit form if all validations pass
            closeAllModal();
        }
    
    });

