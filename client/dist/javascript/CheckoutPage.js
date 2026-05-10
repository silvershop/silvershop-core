(function ($) {

  // Configuration defaults
  if (typeof(window.ShopConfig) != 'object') {
    window.ShopConfig = {};
  }
  if (typeof(window.ShopConfig.Checkout) != 'object') {
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
  function onExistingValueChange() {
    $('.silvershop-has-existing-values').each(function (idx, container) {
      var $toggle = $('.silvershop-existing-values select,.silvershop-existing-values input:checked', container);
      // visible if the value is not an ID (numeric)
      var toggleState = isNaN(parseInt($toggle.val()));
      var toggleMethod = toggleState ? conf.showFieldAnimation : conf.hideFieldAnimation;
      var $toggleFields = $(container).find('.field').not('.silvershop-existing-values');

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

  $('.silvershop-existing-values select').on('change', onExistingValueChange);
  $('.silvershop-existing-values input[type=radio]').on('click', onExistingValueChange);

  onExistingValueChange(); // handle initial state


  $(document).ready(function () {

    // Payment checkout component (selecting a payment method)
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

  });
})(jQuery);
