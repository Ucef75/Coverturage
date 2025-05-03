document.addEventListener('DOMContentLoaded', function() {
    // Error messages
    const errorMessages = {
        'invalid_amount': 'Please enter a valid amount greater than 0',
        'decimal_limit': 'Maximum 2 decimal places allowed'
    };

    // Add funds form validation
    const addFundsForm = document.querySelector('.add-funds-form');
    if (addFundsForm) {
        addFundsForm.addEventListener('submit', function(e) {
            const amountInput = this.querySelector('input[name="amount"]');
            const amount = parseFloat(amountInput.value);
            
            if (isNaN(amount) || amount <= 0) {
                e.preventDefault();
                showError(amountInput, errorMessages['invalid_amount']);
                amountInput.focus();
            }
        });
    }
    
    // Format amount input
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            clearError(this);
            this.value = this.value.replace(/[^0-9.]/g, '');
            
            const decimalCount = this.value.split('.').length - 1;
            if (decimalCount > 1) {
                this.value = this.value.substring(0, this.value.lastIndexOf('.'));
            }
            
            if (this.value.includes('.')) {
                const parts = this.value.split('.');
                if (parts[1].length > 2) {
                    this.value = parts[0] + '.' + parts[1].substring(0, 2);
                    showError(this, errorMessages['decimal_limit']);
                }
            }
        });
    }

    // Helper functions
    function showError(input, message) {
        clearError(input);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'input-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
        input.parentNode.appendChild(errorDiv);
    }

    function clearError(input) {
        const existingError = input.parentNode.querySelector('.input-error');
        if (existingError) existingError.remove();
    }
});