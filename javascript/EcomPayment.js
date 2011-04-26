/**
 * helps in EcommercePayment Selection
 *
 **/
(function($){
	$(window).load(function() {
		EcomPayment.init();
	});
})(jQuery);

var EcomPayment = {

	init: function () {
		var paymentInputs = $('#PaymentMethod input[type=radio]');
		var methodFields = $('div.paymentfields');

		methodFields.hide();

		paymentInputs.each(function(e) {
			if($(this).attr('checked') == true) {
				$('#MethodFields_' + $(this).attr('value')).show();
			}
		});

		paymentInputs.click(function(e) {
			methodFields.hide();
			$('#MethodFields_' + $(this).attr('value')).show();
		});
	}


}
