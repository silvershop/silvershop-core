(function($){

	// Configuration defaults
	if (typeof(window.ShopConfig) != 'object'){
		window.ShopConfig = {};
	}
	if (typeof(window.ShopConfig.Checkout) != 'object'){
		window.ShopConfig.Checkout = {};
	}

	window.ShopConfig.Checkout = $.extend({
		showFieldAnimation: 'fadeIn',
		hideFieldAnimation: 'fadeOut',
	}, window.ShopConfig.Checkout);

	var conf = window.ShopConfig.Checkout;

	// Addressbook checkout component
	// This handles a dropdown or radio buttons containing existing addresses or payment methods,
	// with one of the options being "create a new ____". When that last option is selected, the
	// other fields need to be shown, otherwise they need to be hidden.
	function onExistingValueChange(){
		$('.hasExistingValues').each(function(idx, container){
			var $toggle = $('.existingValues select,.existingValues input:checked', container);
			// visible if the value is not an ID (numeric)
			var toggleState = isNaN(parseInt($toggle.val()));
			var toggleMethod = toggleState ? conf.showFieldAnimation : conf.hideFieldAnimation;
			var $toggleFields = $(container).find('.field').not('.existingValues');

			// animate the fields
			if ($toggleFields && $toggleFields.length > 0) {
				if (typeof(toggleMethod) == 'object') {
					$toggleFields.animate(toggleMethod, 'fast', 'swing');
				} else {
					$toggleFields[toggleMethod]('fast', 'swing');
				}
			}

			// clear them out
			$toggleFields.find('input, select, textarea')
				.val('').prop('disabled', toggleState ? '' : 'disabled');
		});
	}

	$('.existingValues select').on('change', onExistingValueChange);
	$('.existingValues input[type=radio]').on('click', onExistingValueChange);

	onExistingValueChange(); // handle initial state


	$(document).ready(function() {

		// Payment checkout component (selecting a payment method)
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

	});
})(jQuery);
