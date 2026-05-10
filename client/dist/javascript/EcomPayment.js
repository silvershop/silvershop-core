/**
 * helps in EcommercePayment Selection
 *
 **/
(function ($) {
  $(window).load(function () {
    EcomPayment.init();
  });
})(jQuery);

var EcomPayment = {

  init: function () {
    var paymentInputs = $('.silvershop-payment-method input[type=radio]');
    var methodFields = $('.silvershop-payment-fields');

    methodFields.hide();

    paymentInputs.each(function (e) {
      if ($(this).attr('checked') == true) {
        $('.silvershop-payment-fields--' + $(this).attr('value')).show();
      }
    });

    paymentInputs.click(function (e) {
      methodFields.hide();
      $('.silvershop-payment-fields--' + $(this).attr('value')).show();
    });
  }
}
