


const profileModal = document.getElementById('profileDetailsModal');
const dotsBtn = document.querySelector('.CashierDashboard__modal-dots-btn');
const closeBtn = document.getElementById('closeModal');

const logoutBtn = document.getElementById('logoutBtnModal');


dotsBtn.addEventListener('click', function() {
    profileModal.style.display = 'flex';
});

closeBtn.addEventListener('click', function() {
    profileModal.style.display = 'none';
});


logoutBtn.addEventListener('click', function(e) {
    if (confirm('Are you sure you want to log out?')) {
        window.location.href = 'cashier_dashboard.php?action=logout';
    }
});

// ==========================================================================================