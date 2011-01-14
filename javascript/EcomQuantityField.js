/**
  *@description: update cart using AJAX
  */

(function($){
	$(document).ready(
		function() {
			EcomQuantityField.init();
		}
	);

})(jQuery);

EcomQuantityField = {

	hidePlusAndMinus: false,
		set_hidePlusAndMins: function(v) {this.hidePlusAndMinus = v;},

	init: function () {
		jQuery('input.ajaxQuantityField').each(
			function() {
				var inputField = this;
				if(EcomQuantityField.hidePlusAndMinus) {
					jQuery(this).siblings("a.removeOneLink, a.addOneLink").hide();
				}
				else {
					jQuery(this).siblings("a.removeOneLink").click(
						function() {
							jQuery(inputField).val(parseInt(jQuery(inputField).val())-1).change();
							return false;
						}
					);
					jQuery(this).siblings("a.addOneLink").click(
						function() {
							jQuery(inputField).val(parseInt(jQuery(inputField).val())+1).change();
							return false;
						}
					);

				}
				jQuery(this).removeAttr('disabled');
				jQuery(this).change(
					function() {
						var name = jQuery(this).attr('name')+ '_SetQuantityLink';
						var setQuantityLink = jQuery('[name=' + name + ']');
						if(jQuery(setQuantityLink).length > 0) {
							setQuantityLink = jQuery(setQuantityLink).get(0);
							if(! this.value) {
								this.value = 0;
							}
							else {
								this.value = this.value.replace(/[^0-9]+/g, '');
							}
							var url = jQuery('base').attr('href') + setQuantityLink.value + '?quantity=' + this.value;
							jQuery("body").addClass("loading");
							jQuery.getJSON(url, null, Cart.setChanges);
						}
					}
				);
			}
		);

	}
}
