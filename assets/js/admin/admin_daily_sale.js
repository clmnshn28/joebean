    
const logoutSideBarModal = document.getElementById("logoutSideBarModal");

document.querySelector('.Logout__modal-cancel-button').addEventListener('click', function() {
    logoutSideBarModal.style.display = "none";
});

document.querySelector('.AdminItemList__logout').addEventListener('click', function() {
    logoutSideBarModal.style.display = "flex";
});

// =================================================================================

const modal = document.getElementById("itemModal");
const closeModalButton = document.getElementById("closeModal");


// Function to close the modal
closeModalButton.onclick = function() {
    modal.style.display = "none";
}

  // When clicking the view button 
  document.querySelectorAll('.AdminTransactionRecord__table-data-btn').forEach(button => {
    button.addEventListener('click', function() {

        const date = this.getAttribute('data-date');

        fetch('admin_daily_sales.php',{
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_daily_transactions&date=${date}`
        })
        .then(response => response.json())
        .then(data =>{
            console.log("Raw response:", data);
            // Clear the existing table content
            const tableBody = document.querySelector('.AdminDailySales__transaction-items-table tbody');
            tableBody.innerHTML = '';

            const transactions = data.transactions;

            // Add each transaction to the table
            transactions.forEach(transaction => {
                const row = document.createElement('tr');
    
                // Format the transaction date
                const formattedDate = formatDate(transaction.created_at);

                // Prepare payment method display
                let paymentMethodHtml = '';
                if (transaction.payment_method.toLowerCase() === 'gcash') {
                    paymentMethodHtml = `
                        <div class='AdminTransactionRecord__item-with-references'>
                            <p>GCash</p>
                            <p class='AdminTransactionRecord__item-reference-number'>${transaction.ref_no || ''}</p>
                        </div>
                    `;
                } else {
                    paymentMethodHtml = `
                        <div class='AdminTransactionRecord__item-with-references'>
                            <p>${transaction.payment_method.charAt(0).toUpperCase() + transaction.payment_method.slice(1).toLowerCase()}</p>
                        </div>
                    `;
                }

                row.innerHTML = `
                    <td>
                        <div class='AdminDailySales__modal-item-with-image'>
                            <img class='AdminTransactionRecords__item-image' src='../../assets/images/avatars/${transaction.cashier_image}' alt='Cashier Image'>
                            <span>${transaction.cashier_name}</span>
                        </div> 
                    </td>
                    <td>₱${parseFloat(transaction.total_amount).toFixed(2)}</td>
                    <td>${paymentMethodHtml}</td>
                    <td>${formattedDate}</td>
                `;
                tableBody.appendChild(row);

            });

        // You can format the date to make it more user-friendly
        const salesDate = new Date(date);
        const formattedSalesId = salesDate.toLocaleDateString('en-US', {
            month: '2-digit', 
            day: '2-digit', 
            year: 'numeric'
        }).replace(/\//g, '');
      
        document.getElementById('modalSalesId').textContent = formattedSalesId;
            
            modal.style.display = 'flex';
        })
        .catch(error =>{
            console.error("Error fetching daily transaction: ", error);
        })

        modal.style.display = "flex";
    });
});

// Helper function to format date
function formatDate(dateTimeStr) {
    const date = new Date(dateTimeStr);
    
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const year = date.getFullYear();
    
    return `${month}/${day}/${year}`;
}
// =================================================================================
// Pagination functionality
const leftPaginationBtn = document.querySelector('.AdminItemList__pagination-left');
const rightPaginationBtn = document.querySelector('.AdminItemList__pagination-right');
const paginationNumber = document.querySelector('.AdminItemList__pagination-number');

// Current page from URL or default to 1
const urlParams = new URLSearchParams(window.location.search);
const currentPage = parseInt(urlParams.get('page')) || 1;
const currentSearch = urlParams.get('search') || '';
    
// Set search input value from URL parameter if it exists
const searchInput = document.querySelector('.AdminItemList__header-search-container input');
if (currentSearch) {
    searchInput.value = decodeURIComponent(currentSearch);
        
    const length = searchInput.value.length;
    searchInput.setSelectionRange(length, length);
}

// Handle left pagination button click
leftPaginationBtn.addEventListener('click', function() {
    if (currentPage > 1) {
        const newPage = currentPage - 1;
        navigateToPage(newPage);
    }
});

// Handle right pagination button click
rightPaginationBtn.addEventListener('click', function() {
    const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]);
    if (currentPage < totalPages) {
        const newPage = currentPage + 1;
        navigateToPage(newPage);
    }
});

    // Function to navigate to a specific page while preserving search
    function navigateToPage(page) {
    const searchTerm = searchInput.value.trim();
    let url = `admin_daily_sales.php?page=${page}`;
    
    if (searchTerm) {
        url += `&search=${encodeURIComponent(searchTerm)}`;
    }
    window.location.href = url;
}

// Search input handler
searchInput.addEventListener('input', function() {

    clearTimeout(this.searchTimer);

    this.searchTimer = setTimeout(() => {
        navigateToPage(1); // Reset to page 1 when search changes
    }, 800);
});

searchInput.focus();

// Hide pagination if there's only one page
const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]) || 1;
if (totalPages <= 1) {
    document.getElementById('paginationContainer').style.display = 'none';
} else {
    document.getElementById('paginationContainer').style.display = 'flex';
}
