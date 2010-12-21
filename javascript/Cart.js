/**
  *@description: update cart using AJAX
  */

(function($){
	$(document).ready(
		function() {
			ecommerce_cart.updateCartRows();
			jQuery('input.ajaxQuantityField').each(
				function() {
					jQuery(this).removeAttr('disabled');
					jQuery(this).change(
						function() {
							var name = jQuery(this).attr('name')+ '_SetQuantityLink';
							var setQuantityLink = jQuery('[name=' + name + ']');
							if(jQuery(setQuantityLink).length > 0) {
								setQuantityLink = jQuery(setQuantityLink).get(0);
								if(! this.value) this.value = 0;
								else this.value = this.value.replace(/[^0-9]+/g, '');
								var url = jQuery('base').attr('href') + setQuantityLink.value + '?quantity=' + this.value;
								jQuery.getJSON(url, null, ecommerce_cart.setChanges);
							}
						}
					);
				}
			);
			jQuery('select.ajaxCountryField').each(
				function() {
					jQuery(this).removeAttr('disabled');
					jQuery(this).change(
						function() {
							var id = '#' + jQuery(this).attr('id') + '_SetCountryLink';
							var setCountryLink = jQuery(id);
							if(jQuery(setCountryLink).length > 0) {
								setCountryLink = jQuery(setCountryLink).get(0);
								var url = jQuery('base').attr('href') + setCountryLink.value + this.value + "/";
								jQuery.getJSON(url, null, ecommerce_cart.setChanges);
							}
						}
					);
				}
			);
		}
	);

})(jQuery);

ecommerce_cart = {
	setChanges: function (changes) {
		for(var i in changes) {
			var change = changes[i];
			if(typeof(change.parameter) != 'undefined' && typeof(change.value) != 'undefined') {
				var parameter = change.parameter;
				var value = ecommerce_cart.escapeHTML(change.value);
				if(change.id) {
					var id = '#' + change.id;
					if(parameter == 'innerHTML'){
						jQuery(id).html(value);
					}
					else{
						jQuery(id).attr(parameter, value);
					}
				}
				else if(change.name) {
					var name = change.name;
					jQuery('[name=' + name + ']').each(
						function() {
							jQuery(this).attr(parameter, value);
						}
					);
				}
			}
		}
	},

	escapeHTML: function (str) {
		 var div = document.createElement('div');
		 var text = document.createTextNode(str);
		 div.appendChild(text);
		 return div.innerHTML;
	},

	updateCartRows: function() {
		if(jQuery("tr.orderitem").length > 0) {
			jQuery(".showOnZeroItems").hide();
			jQuery(".hideOnZeroItems").show();
		}
		else {
			jQuery(".showOnZeroItems").show();
			jQuery(".hideOnZeroItems").hide();
		}
	}
}
