(function () {
    'use strict';

    function isTableQtyInput(el) {
        return (
            el
            && el.getAttribute('type') === 'number'
            && el.classList
            && el.classList.contains('silvershop-variations-table__qty-input')
            && el.closest('.silvershop-variations-table')
        );
    }

    function clampNegativeQuantityInput(el) {
        if (!isTableQtyInput(el)) {
            return;
        }
        if (el.value === '' || el.value === '-') {
            return;
        }
        var n = parseInt(el.value, 10);
        if (Number.isFinite(n) && n < 0) {
            el.value = '0';
        }
    }

    function preventNonDigitNumberKeys(e) {
        if (e.key === '-' || e.key === '+' || e.key === 'e' || e.key === 'E') {
            e.preventDefault();
        }
    }

    document.addEventListener('keydown', function (e) {
        if (isTableQtyInput(e.target)) {
            preventNonDigitNumberKeys(e);
        }
    });

    document.addEventListener('input', function (e) {
        clampNegativeQuantityInput(e.target);
    });
})();
