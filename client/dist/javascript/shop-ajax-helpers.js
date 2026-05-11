/**
 * Legacy AJAX helpers for ShopQuantityField and coupon modifiers (JSON `changes` payloads).
 * Does not depend on jQuery. Exposes SilverShop.applyCartAjaxChanges and Cart.setChanges for BC.
 */
(function () {
    'use strict';

    function escapeHTML(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /**
     * @param {Array<{parameter?: string, value?: string, id?: string, name?: string}>|unknown} changes
     */
    function applyCartAjaxChanges(changes) {
        if (!changes || typeof changes.length === 'undefined') {
            return;
        }

        for (var i = 0; i < changes.length; i++) {
            var change = changes[i];
            if (
                typeof change !== 'object'
                || change === null
                || typeof change.parameter === 'undefined'
                || typeof change.value === 'undefined'
            ) {
                continue;
            }

            var value = escapeHTML(change.value);

            if (change.id) {
                var el = document.getElementById(change.id);
                if (!el) {
                    continue;
                }

                if (change.parameter === 'innerHTML') {
                    el.innerHTML = value;
                } else {
                    el.setAttribute(change.parameter, value);
                }
            } else if (change.name) {
                document.querySelectorAll('[name]').forEach(function (node) {
                    if (node.getAttribute('name') !== change.name) {
                        return;
                    }

                    node.setAttribute(change.parameter, value);
                });
            }
        }
    }

    function baseHref() {
        var base = document.querySelector('base');
        return base ? base.href || '/' : '/';
    }

    function resolveUrl(pathFromRoot) {
        try {
            return new URL(pathFromRoot.replace(/^\//, ''), baseHref()).toString();
        } catch (e) {
            return pathFromRoot;
        }
    }

    function getJSON(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok) {
                    throw new Error(response.statusText || 'Request failed');
                }
                return body;
            });
        });
    }

    window.SilverShop = window.SilverShop || {};
    window.SilverShop.applyCartAjaxChanges = applyCartAjaxChanges;

    window.Cart = window.Cart || {};
    window.Cart.setChanges = applyCartAjaxChanges;

    function sanitiseQuantity(val) {
        if (!val) {
            return '0';
        }
        return String(val).replace(/[^0-9]+/g, '');
    }

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            document.querySelectorAll('input.silvershop-ajax-quantity-field').forEach(function (input) {
                input.removeAttribute('disabled');
                input.addEventListener('change', function () {
                    var name = input.getAttribute('name');
                    if (!name) {
                        return;
                    }

                    var wanted = name + '_SetQuantityLink';
                    var linkHolder = null;
                    document.querySelectorAll('[name]').forEach(function (node) {
                        if (node.getAttribute('name') === wanted) {
                            linkHolder = node;
                        }
                    });
                    if (!(linkHolder instanceof HTMLInputElement) && !(linkHolder instanceof HTMLSelectElement)) {
                        return;
                    }

                    var path = linkHolder.value;
                    if (!path) {
                        return;
                    }

                    var url = resolveUrl(path);
                    var qty = sanitiseQuantity(input.value);
                    input.value = qty;

                    var sep = url.indexOf('?') >= 0 ? '&' : '?';
                    getJSON(url + sep + 'quantity=' + encodeURIComponent(qty))
                        .then(function (data) {
                            applyCartAjaxChanges(data);
                        })
                        .catch(function () {
                            /* Non-JSON endpoints fall through silently */
                        });
                });
            });

            document.querySelectorAll('select.silvershop-ajax-country-field').forEach(function (select) {
                select.removeAttribute('disabled');
                select.addEventListener('change', function () {
                    var id = select.getAttribute('id');
                    if (!id) {
                        return;
                    }

                    var linkHolder = document.getElementById(id + '_SetCountryLink');
                    if (!(linkHolder instanceof HTMLInputElement) && !(linkHolder instanceof HTMLSelectElement)) {
                        return;
                    }

                    var path = linkHolder.value;
                    if (!path) {
                        return;
                    }

                    var url = resolveUrl(path + '/' + encodeURIComponent(select.value));
                    getJSON(url)
                        .then(function (data) {
                            applyCartAjaxChanges(data);
                        })
                        .catch(function () {});
                });
            });
        },
        false
    );
})();
