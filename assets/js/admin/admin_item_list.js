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
        const form = document.querySelector('#itemModal form'); 
        form.submit(); // Manually submit form if all validations pass
        hideModal();
    }
    

});
