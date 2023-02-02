document.addEventListener('DOMContentLoaded', function() {
	window.cart = new ShoppingCart();
	window.cart.init();
});

function ShoppingCart() {
	let cartSelectors = null;
	let self = this;
	
	// exports
	this.init = init;
	this.reload = reload;
	this.getCart = getCart;
	this.getCarts = getCarts;
	
	function init(cartSelector) {
		if (!cartSelector) {
            cartSelector = '.cart';
		}
		cartSelectors = cartSelector;
		if (!this.getCarts().length) {
			return;
		}
		
        document.body.addEventListener('click', function(e) {
            if (e.target.matches(cartSelectors+' .cart-item-remove')) {
                fetch(e.target.getAttribute('href'), {
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        return;
                    }
                    self.reload();
                });
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            else if (e.target.matches('a.add-to-cart')) {
                fetch(e.target.getAttribute('href'), {
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        return;
                    }
                    self.reload();
                });
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
		
        document.body.addEventListener('submit', function(e) {
            if (e.target.matches('form.add-to-cart')) {
                let form = e.target;
                let formData = new FormData(form);
                let action = null;
                if (typeof e.submitter != "undefined" && e.submitter) {
                    action = e.submitter;
                }
                else {
                    action = form.querySelector(':input[type=submit]:focus');
                }
                if (action) {
                    formData.append(action.getAttribute('name'), 1);
                }
                fetch(form.getAttribute('action'), {
                    body: formData,
                    method: 'post',
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        form.outerHTML = response.responseText;
                        return;
                    }
                    response.text().then(function(data) {
                        if (data) {
                            form.outerHTML = data;
                            let skipCartReload = response.headers.get('x-skipcartreload') ? true : false;
                            if (!skipCartReload) {
                                self.reload();
                            }
                            const event = new CustomEvent('product-form-submitted', {detail: {skipcartreload: skipCartReload}});
                            document.dispatchEvent(event);
                        }
                    });
                });
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
		});
	}
	
	function getCart() {
		return document.querySelector(cartSelectors);
	}

	function getCarts() {
		return document.querySelectorAll(cartSelectors);
	}
	
	function reload() {
        this.getCarts().forEach(function(cart) {
            fetch(cart.getAttribute('data-render-link'), {
				credentials: 'include',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			}).then(function(response) {
				if (!response.ok) {
                    return;
				}
                return response.text();
            }).then(function(data) {
                cart.outerHTML = data;
            });
		});
	}
};