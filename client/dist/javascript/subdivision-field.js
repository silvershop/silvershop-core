/**
 * SubdivisionField: swaps the state / province field between a dropdown and a
 * text input when its country field changes. Does not depend on jQuery.
 */
(function () {
    'use strict';

    var cache = {};

    function fetchSubdivisions(url, country) {
        var key = url + '|' + country;
        if (!cache[key]) {
            cache[key] = fetch(url.replace(/\/$/, '') + '/' + encodeURIComponent(country) + '.json', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.ok ? response.json() : { subdivisions: [] };
                })
                .then(function (data) {
                    return data && data.subdivisions ? data.subdivisions : [];
                })
                .catch(function () {
                    delete cache[key];
                    return [];
                });
        }

        return cache[key];
    }

    function copyAttributes(from, to) {
        for (var i = 0; i < from.attributes.length; i++) {
            var attr = from.attributes[i];
            if (['type', 'value', 'maxlength', 'size', 'class'].indexOf(attr.name) === -1) {
                to.setAttribute(attr.name, attr.value);
            }
        }

        to.className = from.className
            .split(/\s+/)
            .filter(function (name) {
                return name && name !== 'text' && name !== 'dropdown';
            })
            .concat(to.tagName === 'SELECT' ? ['dropdown'] : ['text'])
            .join(' ');
    }

    function buildSelect(field, subdivisions) {
        var select = document.createElement('select');
        var current = String(field.value || '').toLowerCase();
        select.appendChild(new Option('', ''));

        subdivisions.forEach(function (subdivision) {
            var option = new Option(subdivision.name, subdivision.code);
            if (
                current
                && (subdivision.code.toLowerCase() === current || subdivision.name.toLowerCase() === current)
            ) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        return select;
    }

    function buildInput() {
        var input = document.createElement('input');
        input.type = 'text';
        return input;
    }

    function updateField(field, subdivisions) {
        if (!subdivisions.length && field.tagName === 'INPUT') {
            return;
        }

        var replacement = subdivisions.length ? buildSelect(field, subdivisions) : buildInput();
        copyAttributes(field, replacement);
        field.parentNode.replaceChild(replacement, field);
        replacement.dispatchEvent(new Event('change', { bubbles: true }));
    }

    document.addEventListener('change', function (event) {
        var countryField = event.target;
        if (!countryField || !countryField.name || countryField.hasAttribute('data-subdivision-field')) {
            return;
        }

        var scope = countryField.form || document;
        var fields = scope.querySelectorAll('[data-subdivision-field]');
        Array.prototype.forEach.call(fields, function (field) {
            if (field.getAttribute('data-country-field') !== countryField.name) {
                return;
            }

            var country = countryField.value;
            if (!country) {
                updateField(field, []);
                return;
            }

            fetchSubdivisions(field.getAttribute('data-subdivisions-url'), country).then(function (subdivisions) {
                // ignore responses for a country that is no longer selected
                if (countryField.value === country && field.parentNode) {
                    updateField(field, subdivisions);
                }
            });
        });
    });
})();
