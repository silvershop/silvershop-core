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

	quantityFieldSelector: "input.ajaxQuantityField",

	removeSelector: "a.removeOneLink",

	addSelector: "a.addOneLink",

	URLSegmentHiddenFieldSelectorAppendix: "_SetQuantityLink",

	hidePlusAndMinus: false,
		set_hidePlusAndMins: function(v) {this.hidePlusAndMinus = v;},

	init: function () {
		jQuery(EcomQuantityField.quantityFieldSelector).each(
			function() {
				var inputField = this;
				if(EcomQuantityField.hidePlusAndMinus) {
					jQuery(inputField).siblings(EcomQuantityField.removeSelector + ", " + EcomQuantityField.addSelector).hide();
				}
				else {
					jQuery(inputField).siblings(EcomQuantityField.removeSelector).click(
						function() {
							jQuery(inputField).val(parseInt(jQuery(inputField).val())-1).keyup();
							return false;
						}
					);
					jQuery(inputField).siblings(EcomQuantityField.addSelector).click(
						function() {
							jQuery(inputField).val(parseInt(jQuery(inputField).val())+1).keyup();
							return false;
						}
					);

				}
				jQuery(inputField).keyup(
					function() {
						var URLSegment = EcomQuantityField.getSetQuantityURLSegment(this);
						if(URLSegment.length > 0) {
							if(! this.value) {
								this.value = 0;
							}
							else {
								this.value = this.value.replace(/[^0-9]+/g, '');
							}
							var url = jQuery('base').attr('href') + URLSegment + '/?quantity=' + this.value;
							Cart.getChanges(url, null);
						}
						else {
						}
					}
				);
				jQuery(inputField).removeAttr('disabled');
			}
		);
	},

	getSetQuantityURLSegment: function (inputField) {
		var name = jQuery(inputField).attr('name')+EcomQuantityField.URLSegmentHiddenFieldSelectorAppendix ;
		if(jQuery('[name=' + name + ']').length == 1) {
			return jQuery('[name=' + name + ']').val();
		};
		return "";
	},

	debug: function() {
		jQuery(EcomQuantityField.addSelector).css("border", "3px solid red");
		jQuery(EcomQuantityField.removeSelector).css("border", "3px solid red");
		jQuery(EcomQuantityField.quantityFieldSelector).css("border", "3px solid red");
	}
}
