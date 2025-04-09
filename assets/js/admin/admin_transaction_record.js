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
