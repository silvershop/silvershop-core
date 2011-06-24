/**
 * helps in EcommercePayment Selection
 *
 **/
(function(jQuery){
	jQuery(window).load(function() {
		EcomPayment.init();
	});
})(jQuery);

var EcomPayment = {

	init: function () {
		var paymentInputs = jQuery('#PaymentMethod input[type=radio]');
		var methodFields = jQuery('div.paymentfields');

		methodFields.hide();

		paymentInputs.each(function(e) {
			if(jQuery(this).attr('checked') == true) {
				jQuery('#MethodFields_' + jQuery(this).attr('value')).show();
			}
		});

		paymentInputs.click(function(e) {
			methodFields.hide();
			jQuery('#MethodFields_' + jQuery(this).attr('value')).show();
		});
	}


}
