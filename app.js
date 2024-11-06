// app.js

// Confirm booking action
document.addEventListener('DOMContentLoaded', () => {
    const bookingForm = document.querySelector('form');

    if (bookingForm) {
        bookingForm.addEventListener('submit', function (event) {
            // Check-in and check-out date validation
            const checkInDate = new Date(document.querySelector('#check_in').value);
            const checkOutDate = new Date(document.querySelector('#check_out').value);

            if (checkOutDate <= checkInDate) {
                alert("Check-out date must be after the check-in date.");
                event.preventDefault();  // Prevent form submission
            } else {
                // Confirmation alert
                if (!confirm("Are you sure you want to confirm this booking?")) {
                    event.preventDefault();
                }
            }
        });
    }
});
