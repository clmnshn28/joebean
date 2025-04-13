    
const logoutSideBarModal = document.getElementById("logoutSideBarModal");

document.querySelector('.Logout__modal-cancel-button').addEventListener('click', function() {
    logoutSideBarModal.style.display = "none";
});

document.querySelector('.AdminItemList__logout').addEventListener('click', function() {
    logoutSideBarModal.style.display = "flex";
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
    const tableBody = document.getElementById('transactionTableBody');
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
        const transactionCashierName = row.querySelector('td:first-child span')?.textContent.toLowerCase() || '';
        const transactionProductItem = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const transactionPaymentMethod = row.querySelector('td:nth-child(6) p')?.textContent.toLowerCase() || '';
        
        // Check if row contains the search term
        if (transactionCashierName.includes(searchTerm) || transactionProductItem.includes(searchTerm) || transactionPaymentMethod.includes(searchTerm)) {
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
        noResultsRow.innerHTML = `<td colspan="8"><div class="no-data-message" style="text-transform: none;">No items match your search for "${searchTerm}"</div></td>`;
        tableBody.appendChild(noResultsRow);
    }
}


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
