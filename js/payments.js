document.addEventListener('DOMContentLoaded', function() {
     // Add funds form validation
     const addFundsForm = document.querySelector('.add-funds-form');
     if (addFundsForm) {
         addFundsForm.addEventListener('submit', function(e) {
             const amountInput = this.querySelector('input[name="amount"]');
             const amount = parseFloat(amountInput.value);
             
             if (isNaN(amount) || amount <= 0) {
                 e.preventDefault();
                 alert('Please enter a valid amount greater than 0');
                 amountInput.focus();
             }
         });
     }
     
     // Format amount input as user types
     const amountInput = document.querySelector('input[name="amount"]');
     if (amountInput) {
         amountInput.addEventListener('input', function() {
             // Ensure only two decimal places
             if (this.value.includes('.')) {
                 const parts = this.value.split('.');
                 if (parts[1].length > 2) {
                     this.value = parts[0] + '.' + parts[1].substring(0, 2);
                 }
             }
         });
     }
 });