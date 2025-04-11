document.querySelector('.AdminItemList__logout').addEventListener('click', function(e) {
    if (!confirm('Are you sure you want to log out?')) {
        e.preventDefault();
    }
});

// =================================================================================

const modal = document.getElementById("itemModal");
const closeModalButton = document.getElementById("closeModal");

// Get references to modal content
const modalProductImage = document.getElementById('modalProductImage');
const modalTransactionId = document.getElementById('modalTransactionId');
const modalCreatedAt = document.getElementById('modalCreatedAt');
const modalCashierImage = document.getElementById('modalCashierImage');
const modalCashierName = document.getElementById('modalCashierName');
const modalItemName = document.getElementById('modalItemName');
const modalItemCategory = document.getElementById('modalItemCategory');
const modalItemPrice = document.getElementById('modalItemPrice');
const modalItemQuantity = document.getElementById('modalItemQuantity');
const modalItemAmount = document.getElementById('modalItemAmount');


// When clicking the view button
document.querySelectorAll('.AdminTransactionRecord__table-data-btn').forEach(button => {
    button.addEventListener('click', function () {
        modalProductImage.src = '../../assets/images/products/' + this.dataset.productimage;
        modalTransactionId.textContent = this.dataset.transactionid;
        modalCreatedAt.textContent = this.dataset.createdat;
        modalCashierImage.src = '../../assets/images/avatars/' + this.dataset.cashierimage;
        modalCashierName.textContent = this.dataset.cashiername;
        modalItemName.textContent = this.dataset.itemname;
        modalItemCategory.textContent = this.dataset.itemcategory;
        modalItemPrice.textContent = this.dataset.itemprice;
        modalItemQuantity.textContent = this.dataset.itemquantity;
        modalItemAmount.textContent = this.dataset.itemamount;
        // Check if reference number exists
        if (this.dataset.referencenumber && this.dataset.referencenumber.trim() !== '') {
            document.querySelector('#modalPaymentMethod').innerHTML = 
                this.dataset.paymentmethod + ' <span class="AdminTransactionRecord__reference-number" id="modalReferenceNumber">' + 
                this.dataset.referencenumber + '</span>';
        } else {
            // Just show the payment method without reference number
            document.querySelector('#modalPaymentMethod').textContent = this.dataset.paymentmethod;
        }

        modal.style.display = 'flex';
    });
});

// Function to close the modal
closeModalButton.onclick = function() {
    modal.style.display = "none";
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
        window.location.href = `admin_transaction_record.php?page=${newPage}`;
    }
});

// Handle right pagination button click
rightPaginationBtn.addEventListener('click', function() {
    const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]);
    if (currentPage < totalPages) {
        const newPage = currentPage + 1;
        window.location.href = `admin_transaction_record.php?page=${newPage}`;
    }
});

// Hide pagination if there's only one page
const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]) || 1;
if (totalPages <= 1) {
    document.getElementById('paginationContainer').style.display = 'none';
} else {
    document.getElementById('paginationContainer').style.display = 'flex';
}
