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

	classToShowLoading: "loadingCartData",

	attachClassTo: "body",

	init: function () {
		Cart.updateCartRows();
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
							Cart.getChanges(url, null);
						}
					}
				);
			}
		);
	},

	getChanges: function(url, params) {
		jQuery(Cart.attachClassTo).addClass(Cart.classToShowLoading);
		jQuery.getJSON(url, params, Cart.setChanges);
	},

	setChanges: function (changes) {
		for(var i in changes) {
			var change = changes[i];
			if(typeof(change.parameter) != 'undefined' && typeof(change.value) != 'undefined') {
				var parameter = change.parameter;
				var value = Cart.escapeHTML(change.value);
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
		jQuery(Cart.attachClassTo).removeClass(Cart.classToShowLoading);
	},
	//to do: remove ... we dont seem to need it!
	escapeHTML: function (str) {
		 return str;
	},
	//to do: explain this function
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
