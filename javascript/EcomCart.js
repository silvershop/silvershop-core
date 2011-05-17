/**
  *@description: update Cart using AJAX (JSON data source)
  **/

(function($){
	$(document).ready(
		function() {
			EcomCart.init();
		}
	);

})(jQuery);

EcomCart = {

	setCountryLinkAppendix: "_SetCountryLink",

	ajaxCountryFieldSelector: "select.ajaxCountryField",

	classToShowLoading: "loadingCartData",

	attachLoadingClassTo: "body",

	selectorShowOnZeroItems: ".showOnZeroItems",

	selectorHideOnZeroItems: ".hideOnZeroItems",

	selectorItemRows: "tr.orderitem",

	init: function () {
		EcomCart.updateForZeroVSOneOrMoreRows();
		jQuery(EcomCart.ajaxCountryFieldSelector).each(
			function() {
				jQuery(this).removeAttr('disabled');
				jQuery(this).change(
					function() {
						var id = '#' + jQuery(this).attr('id') + EcomCart.setCountryLinkAppendix;
						var setCountryLink = jQuery(id);
						if(jQuery(setCountryLink).length > 0) {
							setCountryLink = jQuery(setCountryLink).get(0);
							var url = jQuery('base').attr('href') + setCountryLink.value + this.value + "/";
							EcomCart.getChanges(url, null);
						}
					}
				);
			}
		);
	},

	//get JSON data from server
	getChanges: function(url, params) {
		jQuery(EcomCart.attachLoadingClassTo).addClass(EcomCart.classToShowLoading);
		jQuery.getJSON(url, params, EcomCart.setChanges);
	},

	//sets changes to Cart
	setChanges: function (changes) {
		EcomCart.updateForZeroVSOneOrMoreRows();
		for(var i in changes) {
			var change = changes[i];
			if(typeof(change.parameter) != 'undefined' && typeof(change.value) != 'undefined') {
				var parameter = change.parameter;
				var value = EcomCart.escapeHTML(change.value);
				//selector Types
				var id = change.id;
				var name = change.name;
				var selector = change.selector;
				if(EcomCart.variableSetWithValue(id)) {
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
					else if(EcomCart.variableSetWithValue(change.isOrderMessage)) {
						jQuery(id).html(value);
					}
					else if(parameter == 'innerHTML'){
						jQuery(id).html(value);
					}
					else{
						jQuery(id).attr(parameter, value);
					}
				}
				//used for form fields...
				else if(EcomCart.variableSetWithValue(name)) {
					jQuery('[name=' + name + ']').each(
						function() {
							jQuery(this).attr(parameter, value);
						}
					);
				}
				//user for class elements
				else if(EcomCart.variableSetWithValue(selector)) {
					jQuery(selector).each(
						function() {
							jQuery(this).attr(parameter, value);
						}
					);
				}
			}
		}
		jQuery(EcomCart.attachLoadingClassTo).removeClass(EcomCart.classToShowLoading);
	},

	escapeHTML: function (str) {
		return str;
	},

	//if there are no items in the cart - then we hide the cart and we show a row saying: "nothing in cart"
	updateForZeroVSOneOrMoreRows: function() {
		if(EcomCart.CartHasItems()) {
			jQuery(EcomCart.selectorShowOnZeroItems).hide();
			jQuery(EcomCart.selectorHideOnZeroItems).show();
		}
		else {
			jQuery(EcomCart.selectorShowOnZeroItems).show();
			jQuery(EcomCart.selectorHideOnZeroItems).hide();
		}
	},

	//check if there are any items in the cart
	CartHasItems: function() {
		return jQuery(EcomCart.selectorItemRows).length > 0;
	},

	//check if a variable "isset"
	variableIsSet: function(variable) {
		if(typeof(variable) == 'undefined' || typeof variable == 'undefined' || variable == 'undefined') {
			return false;
		}
		return true;
	},

	//variables isset AND it has a value....
	variableSetWithValue: function(variable) {
		if(EcomCart.variableIsSet(variable)) {
			if(variable) {
				return true;
			}
		}
		return false;
	}

}


