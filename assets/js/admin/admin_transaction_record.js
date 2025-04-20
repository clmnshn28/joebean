        
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

    // When clicking the view button 
    document.querySelectorAll('.AdminTransactionRecord__table-data-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Get the transaction ID from the row
            const transactionId = this.closest('tr').getAttribute('data-transaction-id');
            
            // Fetch transaction details using AJAX
            fetch('admin_transaction_record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_transaction_details&transaction_id=${transactionId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Raw response:", data); 
                // Populate the modal with transaction details
                const transaction = data.transaction;
                const items = data.items;
                
                // Update transaction header information
                document.getElementById('modalCashierName').textContent = transaction.cashier_name;
                document.getElementById('modalCashierImage').src = `../../assets/images/avatars/${transaction.cashier_image}`;
                document.getElementById('modalTransactionCreated').textContent = formatDateTime(transaction.created_at);
                document.getElementById('modalTransactionId').textContent = transaction.transaction_id;
                
                // Update payment method with reference number if applicable
                const paymentMethodElement = document.getElementById('modalPaymentMethod');
                if (transaction.payment_method.toLowerCase() === 'gcash') {
                    paymentMethodElement.innerHTML = `GCash <span class="AdminTransactionRecord__reference-number">${transaction.ref_no}</span>`;
                } else {
                    paymentMethodElement.textContent = transaction.payment_method.charAt(0).toUpperCase() + transaction.payment_method.slice(1).toLowerCase();
                }
                
                // Clear and populate items table
                const tableBody = document.querySelector('.AdminTransactionRecord__transaction-items-table tbody');
                tableBody.innerHTML = '';
                
                items.forEach(item => {
                    const row = document.createElement('tr');
                    const itemSize = item.item_size ? item.item_size : "-";
                    const itemSizeStyle = itemSize === "-" ? "padding-left: 32px;" : "";

                    row.innerHTML = `
                        <td>
                            <div class='AdminTransactionRecord__modal-item-with-image'>
                                <img class='AdminTransactionRecords__item-image' src='../../assets/images/products/${item.item_image}' alt='Item Image'>
                                <span>${item.item_name}</span>
                            </div>
                        </td>
                        <td>${item.item_category}</td>
                        <td style="${itemSizeStyle}">${itemSize}</td>
                        <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>₱${parseFloat(item.item_total).toFixed(2)}</td>
                    `;
                    tableBody.appendChild(row);
                });
                
                // Show the modal
                modal.style.display = 'flex';
            })
            .catch(error => {
                console.error('Error fetching transaction details:', error);
            });
        });
    });

    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
    
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
        const month = months[date.getMonth()];
        const day = date.getDate();
        const year = date.getFullYear();
    
        let hour = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
    
        return `${month} ${day}, ${year} — ${hour}:${minutes} ${ampm}`;
    }

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
            // window.location.href = `admin_transaction_record.php?page=${newPage}`;
            navigateToPage(newPage);
        }
    });

    // Handle right pagination button click
    rightPaginationBtn.addEventListener('click', function() {
        const totalPages = parseInt(paginationNumber.textContent.split(' of ')[1]);
        if (currentPage < totalPages) {
            const newPage = currentPage + 1;
            // window.location.href = `admin_transaction_record.php?page=${newPage}`;
            navigateToPage(newPage);
        }
    });

     // Function to navigate to a specific page while preserving search
     function navigateToPage(page) {
        const searchTerm = searchInput.value.trim();
        let url = `admin_transaction_record.php?page=${page}`;
        
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
