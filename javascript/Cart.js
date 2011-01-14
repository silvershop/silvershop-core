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
							jQuery("body").addClass("loading");
							jQuery.getJSON(url, null, Cart.setChanges);
						}
					}
				);
			}
		);

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
		jQuery("body").removeClass("loading");
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
