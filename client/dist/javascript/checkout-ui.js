(function () {
    'use strict';

    function extendCheckoutDefaults(target, source) {
        var out = target || {};
        if (!source) {
            return out;
        }

        Object.keys(source).forEach(function (k) {
            if (typeof out[k] === 'undefined') {
                out[k] = source[k];
            }
        });
        return out;
    }

    window.ShopConfig = window.ShopConfig || {};
    window.ShopConfig.Checkout = extendCheckoutDefaults(window.ShopConfig.Checkout, {
        showFieldAnimation: 'show',
        hideFieldAnimation: 'hide'
    });

    function togglePaymentFields() {
        var methodFields = document.querySelectorAll('.silvershop-payment-fields');
        methodFields.forEach(function (el) {
            el.style.display = 'none';
        });

        var checked =
            document.querySelector('.silvershop-payment-method input[type="radio"]:checked')
            || document.querySelector('.silvershop-payment-method input[type="radio"][checked]');

        if (!checked) {
            return;
        }

        var key = String(checked.value);
        var escaped =
            typeof CSS !== 'undefined' && typeof CSS.escape === 'function'
                ? CSS.escape(key)
                : key.replace(/\\/g, '\\\\');

        var show = document.querySelector('.silvershop-payment-fields--' + escaped);
        if (show) {
            show.style.display = '';
        }
    }

    function clearFields(fields, enabled) {
        fields.forEach(function (field) {
            field.querySelectorAll('input, select, textarea').forEach(function (f) {
                if (enabled) {
                    f.removeAttribute('disabled');
                } else {
                    f.setAttribute('disabled', 'disabled');
                    if (f instanceof HTMLInputElement || f instanceof HTMLTextAreaElement) {
                        f.value = '';
                    } else if (f instanceof HTMLSelectElement) {
                        f.selectedIndex = 0;
                    }
                }
            });
        });
    }

    function onExistingValueChange() {
        document.querySelectorAll('.silvershop-has-existing-values').forEach(function (container) {
            var toggle = container.querySelector(
                '.silvershop-existing-values select, .silvershop-existing-values input:checked'
            );

            if (!toggle) {
                return;
            }

            var raw = toggle.value;
            var parsed = parseInt(raw, 10);
            var toggleState = Number.isNaN(parsed);

            var toggleFields = container.querySelectorAll('.field:not(.silvershop-existing-values)');
            toggleFields.forEach(function (field) {
                field.style.display = toggleState ? '' : 'none';
            });

            clearFields(toggleFields, toggleState);
        });
    }

    document.addEventListener(
        'change',
        function (e) {
            var t = e.target;
            if (!(t instanceof Element)) {
                return;
            }

            if (t.closest('.silvershop-payment-method')) {
                togglePaymentFields();
            }

            if (
                (t.matches && t.matches('.silvershop-existing-values select'))
                || (t.matches && t.matches('.silvershop-existing-values input[type="radio"]'))
            ) {
                onExistingValueChange();
            }
        },
        false
    );

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            onExistingValueChange();
            togglePaymentFields();

            document.querySelectorAll('.silvershop-payment-method input[type="radio"]').forEach(function (input) {
                input.addEventListener('click', togglePaymentFields);
            });
        },
        false
    );
})();
