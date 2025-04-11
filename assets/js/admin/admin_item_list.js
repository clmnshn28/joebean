document.querySelector('.AdminItemList__logout').addEventListener('click', function(e) {
    if (!confirm('Are you sure you want to log out?')) {
        e.preventDefault();
    }
});

// =================================================================================

const modal = document.getElementById('itemModal');
const openModalBtn = document.getElementById('openModalBtn');
const cancelButton = document.querySelector('.AdminItemList__modal-cancel-button');

// Function to show the modal
function showModal() {
    modal.style.display = 'flex';
}

// Function to hide the modal
function hideModal() {
    modal.style.display = 'none';
    const form = document.querySelector('#itemModal form'); 
    form.reset();
    const imagePreview = document.getElementById('item-preview');
    imagePreview.src = '../../assets/images/image-preview.jpg';
}

openModalBtn.addEventListener('click', showModal);
cancelButton.addEventListener('click', hideModal);


// =================================================================================

const itemImageInput = document.getElementById('item-image');
const previewImg = document.getElementById('item-preview');

itemImageInput.addEventListener('change', function () {
    const file = itemImageInput.files[0];
    if (file) {
        previewImg.src = URL.createObjectURL(file);
    }
});

// =================================================================================

function allowNumbersOnly(event) {
  
    event.target.value = event.target.value.replace(/[^0-9]/g, '');
}

document.getElementById("item-price").addEventListener("input", allowNumbersOnly);
document.getElementById("item-stock").addEventListener("input",allowNumbersOnly);
document.getElementById("item-price1").addEventListener("input", allowNumbersOnly);
document.getElementById("item-stock1").addEventListener("input",allowNumbersOnly);

document.getElementById("item-edit-price").addEventListener("input", allowNumbersOnly);
document.getElementById("item-edit-stock").addEventListener("input",allowNumbersOnly);
document.getElementById("item-edit-price1").addEventListener("input", allowNumbersOnly);
document.getElementById("item-edit-stock1").addEventListener("input",allowNumbersOnly);


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
        window.location.href = `admin_item_list.php?page=${newPage}`;
    }
});

// Handle right pagination button click
rightPaginationBtn.addEventListener('click', function() {
    const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]);
    if (currentPage < totalPages) {
        const newPage = currentPage + 1;
        window.location.href = `admin_item_list.php?page=${newPage}`;
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


const addItemBtn = document.querySelector(".AdminItemList__modal-save-button");

addItemBtn.addEventListener("click", function (event) {
    event.preventDefault(); // Prevent form submission for validation first
 
    const form = document.querySelector('#itemModal form'); 
    if (!form.checkValidity()) {
        form.reportValidity();  // Triggers the browser's built-in validation UI
        return;
    }
 
    const avatarFile = itemImageInput.files[0];
    const imageError = document.querySelector('.error-image-message');
    // Reset image error message
    imageError.textContent = "";
    let isValid = true;

    // Check if image is selected
    const defaultPreview = "../../assets/images/image-preview.jpg";
    const previewSrc = previewImg.getAttribute("src");
    
    if (!avatarFile && previewSrc === defaultPreview) {
        imageError.style.display = "block";
        imageError.textContent = "Image is required.";
        isValid = false;
    } else {
        imageError.style.display = "none";
    }

    if (isValid ) {
        form.submit(); // Manually submit form if all validations pass
        hideModal();
    }
    

});



// =================================================================================

const selectTrigger = document.getElementById('category-trigger');
const customSelectContainer = document.querySelector('.custom-select-container');
const customOptions = document.querySelectorAll('.custom-option');
const originalSelect = document.getElementById('item-category');

// Toggle dropdown when clicking on trigger
selectTrigger.addEventListener('click', function() {
    customSelectContainer.classList.toggle('active');
});

// Handle option selection
customOptions.forEach(option => {
    option.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        
        // Save the current image
        const arrowImage = selectTrigger.querySelector('img');
        
        // Update the trigger text but preserve the image
        selectTrigger.innerHTML = this.textContent;
        
        // Add the image back
        selectTrigger.appendChild(arrowImage);
        
        // Update the original select 
        if (originalSelect) {
            originalSelect.value = value;
        }


        // Close the dropdown
        customSelectContainer.classList.remove('active');
    });
});


// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!customSelectContainer.contains(e.target)) {
        customSelectContainer.classList.remove('active');
    }
});


// =================================================================================


const modalEdit = document.getElementById('itemEditModal');
const openModalEditBtn = document.getElementById('openEditModalBtn');
const cancelEditButton = document.getElementById('cancelEditButton');


// Function to show the modal
function showEditModal() {
    modalEdit.style.display = 'flex';
}

// Function to hide the modal
function hideEditModal() {
    modalEdit.style.display = 'none';
}

cancelEditButton.addEventListener('click', hideEditModal);

// Get all edit buttons
const editButtons = document.querySelectorAll('.AdminItemList__table-edit-data-btn');
    
// Add click event listener to each edit button
editButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Get data from data attributes
        const productId = this.getAttribute('data-product-id');
        const name = this.getAttribute('data-name');
        const category = this.getAttribute('data-category');
        const sizes = this.getAttribute('data-sizes').split(',');
        const prices = this.getAttribute('data-prices').split(',');
        const stocks = this.getAttribute('data-stocks').split(',');
        const image = this.getAttribute('data-image');
        
        // Fill the edit modal with data
        document.getElementById('edit-product-id').value = productId;
        document.getElementById('item-edit-name').value = name;
        document.getElementById('item-edit-category').value = category;
        
        // Update the category trigger text to display the currently selected category
        const categoryTrigger = document.getElementById('category-edit-trigger');
        categoryTrigger.innerHTML = category + '<img src="../../assets/images/dropdown-icon.svg" alt="dropdown icon">';
        

        // Fill sizes, prices, and stocks (assuming you have two inputs for each)
        document.querySelector('input[name="item-edit-size1"]').value = sizes[0] || '';
        document.querySelector('input[name="item-edit-size2"]').value = sizes[1] || '';
        
        document.querySelector('input[name="item-edit-price1"]').value = prices[0] || '';
        document.querySelector('input[name="item-edit-price2"]').value = prices[1] == 0 ? '' : prices[1];

        document.querySelector('input[name="item-edit-stock1"]').value = stocks[0] || '';
        document.querySelector('input[name="item-edit-stock2"]').value = stocks[1] == 0 ? '' : stocks[1];
        
        document.getElementById('current-image').value = image;
        // Set the image preview
        const previewEditImg = document.getElementById('item-edit-preview');
        if (image) {
            previewEditImg.src = '../../assets/images/products/' + image;
        } else {
            previewEditImg.src = '../../assets/images/image-preview.jpg';
        }
        
        // Show the edit modal
        showEditModal();
    });
});


// =================================================================================

const selectEditTrigger = document.getElementById('category-edit-trigger');
const customEditSelectContainer = document.querySelector('#custom-edit-container');
const customEditOptions = document.querySelectorAll('.custom-edit-option');
const originalEditSelect = document.getElementById('item-edit-category');

// Toggle dropdown when clicking on trigger
selectEditTrigger.addEventListener('click', function() {
    customEditSelectContainer.classList.toggle('active');
});

// Handle option selection
customEditOptions.forEach(option => {
    option.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        
        // Save the current image
        const arrowImage = selectEditTrigger.querySelector('img');
        
        // Update the trigger text but preserve the image
        selectEditTrigger.innerHTML = this.textContent;
        
        // Add the image back
        selectEditTrigger.appendChild(arrowImage);
        
        // Update the original select 
        if (originalEditSelect) {
            originalEditSelect.value = value;
        }


        // Close the dropdown
        customEditSelectContainer.classList.remove('active');
    });
});


// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!customEditSelectContainer.contains(e.target)) {
        customEditSelectContainer.classList.remove('active');
    }
});



// =================================================================================

const itemEditImageInput = document.getElementById('item-edit-image');
const previewEditImg = document.getElementById('item-edit-preview');

itemEditImageInput.addEventListener('change', function () {
    const file = itemEditImageInput.files[0];
    if (file) {
        previewEditImg.src = URL.createObjectURL(file);
    }
});

// =================================================================================

const editSaveItemBtn = document.getElementById("saveEditButton");

editSaveItemBtn.addEventListener("click", function (event) {
    event.preventDefault(); // Prevent form submission for validation first
 
    const form = document.querySelector('#itemEditModal form'); 
    if (!form.checkValidity()) {
        form.reportValidity();  // Triggers the browser's built-in validation UI
        return;
    }
 
    // Current image is already set, no validation needed if keeping existing image
    const currentImage = document.getElementById('current-image').value;
        
    if (!currentImage && !itemEditImageInput.files[0]) {
        const imageError = document.querySelector('#itemEditModal .error-image-message');
        imageError.style.display = "block";
        imageError.textContent = "Image is required.";
        return;
    }
    
    form.submit();
    
});




// =================================================================================

// Delete functionality
const deleteModal = document.getElementById('deleteItemModal');
const deleteButtons = document.querySelectorAll('.AdminItemList__table-delete-data-btn');
const cancelDeleteButton = document.querySelector('.AdminItemList__modal-delete-cancel-pass-button');

// Function to show delete modal
function showDeleteModal() {
    deleteModal.style.display = 'flex';
}

// Function to hide delete modal
function hideDeleteModal() {
    deleteModal.style.display = 'none';
}


cancelDeleteButton.addEventListener('click', hideDeleteModal);


deleteButtons.forEach(button => {
    button.addEventListener('click', function() {

        // Get data from data attributes
        const productId = this.getAttribute('data-product-id');
        const itemName = this.getAttribute('data-name');
              
        document.getElementById('deleteItemName').textContent = itemName;
        document.getElementById('deleteProductId').value = productId;

        // Show the delete modal
        showDeleteModal();
    });
});