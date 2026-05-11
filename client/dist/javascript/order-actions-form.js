(function () {
    'use strict';

    function parseGatewaysJson(span) {
        try {
            return JSON.parse(span.getAttribute('data-gateways') || '{}');
        } catch (e) {
            return {};
        }
    }

    function handleGatewayChanged(formRoot) {
        var selectedEl = formRoot.querySelector('.silvershop-payment-method input:checked');
        var selected = selectedEl ? selectedEl.value : null;

        var ccInput = formRoot.querySelector('.silvershop-credit-card');
        if (!ccInput) {
            return;
        }

        var lookupEl = ccInput.querySelector('.silvershop-gateway-lookup');
        var lookup = lookupEl ? parseGatewaysJson(lookupEl) : {};

        if (selected && lookup && Object.prototype.hasOwnProperty.call(lookup, selected)) {
            ccInput.style.display = '';
            ccInput.querySelectorAll('.field').forEach(function (field) {
                field.style.display = 'none';
            });

            var names = lookup[selected];
            if (Array.isArray(names)) {
                names.forEach(function (name) {
                    Array.prototype.forEach.call(ccInput.querySelectorAll('[name]'), function (inp) {
                        if (inp.getAttribute('name') !== name) {
                            return;
                        }

                        var field = inp.closest('.field');
                        if (field) {
                            field.style.display = '';
                        }
                    });
                });
            }
        } else {
            ccInput.style.display = 'none';
        }
    }

    function bindForm(form) {
        if (!form.querySelector('.silvershop-payment-method')) {
            return;
        }

        var handler = function () {
            handleGatewayChanged(form);
        };

        form.querySelectorAll('.silvershop-payment-method input').forEach(function (inp) {
            inp.addEventListener('change', handler);
        });
        handler();
    }

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            document.querySelectorAll('form').forEach(bindForm);
        },
        false
    );
})();
