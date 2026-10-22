/* Transactions form behavior — show/hide category select based on type */
(function() {
    'use strict';
    const form = document.querySelector('form[action="/transactions"]');
    if (!form) return;

    const typeInputs = form.querySelectorAll('input[name="type"]');
    const categoryField = form.querySelector('#category_id')?.closest('.field');
    const paymentLabel = form.querySelector('label[for="payment_method"]');

    function updateVisibility() {
        const selected = form.querySelector('input[name="type"]:checked')?.value;
        if (selected === 'expense' || selected === 'refund') {
            if (categoryField) categoryField.style.display = '';
        } else {
            if (categoryField) categoryField.style.display = 'none';
        }
    }

    typeInputs.forEach(function(input) {
        input.addEventListener('change', updateVisibility);
    });
    updateVisibility();
})();
