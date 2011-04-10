/**
  *@description: update cart using AJAX
  */

(function($){
	$(document).ready(
		function() {
			Cart.init();
		}
	);

})(jQuery);

Cart = {

	setCountryLinkAppendix: "_SetCountryLink",

	classToShowLoading: "loadingCartData",

	attachLoadingClassTo: "body",

	cartMessageClass: "cartMessage",

	selectorShowOnZeroItems: ".showOnZeroItems",

	selectorHideOnZeroItems: ".hideOnZeroItems",

	selectorItemRows: "tr.orderitem",

	init: function () {
		Cart.updateCartRows();
		jQuery('select.ajaxCountryField').each(
			function() {
				jQuery(this).removeAttr('disabled');
				jQuery(this).change(
					function() {
						var id = '#' + jQuery(this).attr('id') + Cart.setCountryLinkAppendix;
						var setCountryLink = jQuery(id);
						if(jQuery(setCountryLink).length > 0) {
							setCountryLink = jQuery(setCountryLink).get(0);
							var url = jQuery('base').attr('href') + setCountryLink.value + this.value + "/";
							Cart.getChanges(url, null);
						}
					}
				);
			}
		);
	},

	getChanges: function(url, params) {
		jQuery(Cart.attachLoadingClassTo).addClass(Cart.classToShowLoading);
		jQuery.getJSON(url, params, Cart.setChanges);
	},

	setChanges: function (changes) {
		Cart.updateCartRows();
		for(var i in changes) {
			var change = changes[i];
			if(typeof(change.parameter) != 'undefined' && typeof(change.value) != 'undefined') {
				var parameter = change.parameter;
				var value = Cart.escapeHTML(change.value);
				//selector Types
				var id = change.id;
				var name = change.name;
				var selector = change.selector;
				if(Cart.variableSetWithValue(id)) {
					var id = '#' + id;
					//hide or show row...
					if(parameter == "hide") {
						if(change.value) {
							jQuery(id).hide();
						}
						else {
							jQuery(id).show();
						}
					}
					//general message
					else if(Cart.variableSetWithValue(change.isOrderMessage)) {
						jQuery(id).html('<span class="'+ change.messageClass+'">' + value +'</span>');
					}
					else if(parameter == 'innerHTML'){
						jQuery(id).html(value);
					}
					else{
						jQuery(id).attr(parameter, value);
					}
				}
				//used for form fields...
				else if(Cart.variableSetWithValue(name)) {
					jQuery('[name=' + name + ']').each(
						function() {
							jQuery(this).attr(parameter, value);
						}
					);
				}
				//user for class elements
				else if(Cart.variableSetWithValue(selector)) {
					jQuery(selector).each(
						function() {
							jQuery(this).attr(parameter, value);
						}
					);
				}
			}
		}
		jQuery(Cart.attachLoadingClassTo).removeClass(Cart.classToShowLoading);
	},
	//to do: remove ... we dont seem to need it!
	escapeHTML: function (str) {
		return str;
	},
	//to do: explain this function
	updateCartRows: function() {
		if(Cart.cartHasItems()) {
			jQuery(Cart.selectorShowOnZeroItems).hide();
			jQuery(Cart.selectorHideOnZeroItems).show();
		}
		else {
			jQuery(Cart.selectorShowOnZeroItems).show();
			jQuery(Cart.selectorHideOnZeroItems).hide();
		}
	},

	cartHasItems: function() {
		return jQuery(Cart.selectorItemRows).length > 0;
	},

	variableSet: function(variable) {
		if(typeof(variable) == 'undefined' || typeof variable == 'undefined' || variable == 'undefined') {
			return false;
		}
		return true;
	},

	variableSetWithValue: function(variable) {
		if(Cart.variableSet(variable)) {
			if(variable) {
				return true;
			}
		}
		return false;
	}

}
