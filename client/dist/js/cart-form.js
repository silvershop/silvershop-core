/**
 * Cart table: +/- quantity and remove via CartPage JSON actions (?format=json).
 */
(function () {
  'use strict';

  /**
   * @param {string} relativeOrAbsolute
   * @returns {URL}
   */
  function parseUrl(relativeOrAbsolute) {
    return new URL(relativeOrAbsolute, window.location.href);
  }

  /**
   * @param {string} baseUrl from data-set-quantity-url / data-remove-url
   * @param {Record<string, string>} setParams
   * @returns {string}
   */
  function urlWithParams(baseUrl, setParams) {
    const u = parseUrl(baseUrl);
    Object.keys(setParams).forEach(function (k) {
      u.searchParams.set(k, setParams[k]);
    });
    return u.pathname + u.search + u.hash;
  }

  /**
   * @param {string} href
   * @returns {Promise<Record<string, unknown>>}
   */
  function getJson(href) {
    return fetch(href, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    }).then(function (response) {
      return response.json().then(function (body) {
        if (!response.ok) {
          const msg =
            typeof body.message === 'string' && body.message !== ''
              ? body.message
              : response.statusText || 'Request failed';
          throw new Error(msg);
        }
        return body;
      });
    });
  }

  /**
   * @param {HTMLElement} wrap
   * @param {boolean} busy
   */
  function setQtyBusy(wrap, busy) {
    wrap.classList.toggle('silvershop-cart__qty--busy', busy);
    wrap.querySelectorAll('button, .silvershop-cart__qty-input').forEach(function (el) {
      el.toggleAttribute('disabled', busy);
    });
  }

  /**
   * @param {HTMLElement} wrap
   * @param {number} quantity
   */
  function applyQuantity(wrap, quantity) {
    const base = wrap.getAttribute('data-set-quantity-url');
    if (!base) {
      return;
    }
    const q = Math.max(0, Math.floor(Number(quantity)) || 0);
    const href = urlWithParams(base, { quantity: String(q), format: 'json' });
    setQtyBusy(wrap, true);
    getJson(href)
      .then(function () {
        window.location.reload();
      })
      .catch(function (err) {
        setQtyBusy(wrap, false);
        window.alert(err.message || String(err));
      });
  }

  /**
   * @param {HTMLButtonElement} button
   */
  function removeLine(button) {
    const base = button.getAttribute('data-remove-url');
    if (!base) {
      return;
    }
    button.disabled = true;
    const href = urlWithParams(base, { format: 'json' });
    getJson(href)
      .then(function () {
        window.location.reload();
      })
      .catch(function (err) {
        button.disabled = false;
        window.alert(err.message || String(err));
      });
  }

  document.addEventListener(
    'click',
    function (e) {
      const t = e.target;
      if (!(t instanceof Element)) {
        return;
      }
      const dec = t.closest('.silvershop-cart__qty-btn--dec');
      const inc = t.closest('.silvershop-cart__qty-btn--inc');
      if (dec || inc) {
        e.preventDefault();
        const btn = /** @type {HTMLElement} */ (dec || inc);
        const wrap = btn.closest('[data-cart-qty]');
        if (!wrap) {
          return;
        }
        const input = wrap.querySelector('.silvershop-cart__qty-input');
        const current = parseInt(String(input && input.value), 10);
        const base = Number.isFinite(current) ? current : 0;
        const next = inc ? base + 1 : Math.max(0, base - 1);
        applyQuantity(wrap, next);
        return;
      }

      const removeBtn = t.closest('[data-cart-remove]');
      if (removeBtn instanceof HTMLButtonElement) {
        e.preventDefault();
        removeLine(removeBtn);
      }
    },
    false
  );

  document.addEventListener(
    'change',
    function (e) {
      const t = e.target;
      if (!(t instanceof HTMLInputElement)) {
        return;
      }
      if (!t.classList.contains('silvershop-cart__qty-input')) {
        return;
      }
      const wrap = t.closest('[data-cart-qty]');
      if (!(wrap instanceof HTMLElement)) {
        return;
      }
      const raw = t.value.trim();
      const q = raw === '' ? 0 : Math.max(0, Math.floor(Number(raw)) || 0);
      applyQuantity(wrap, q);
    },
    false
  );
})();

