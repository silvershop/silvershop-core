(function ($) {
  $(function () {
    // Helper JavaScript to toggle Credit-Card fields, depending on selected gateway
    var handleGatewayChanged = function () {
      // Get the currently selected gateway
      var selected = $("#PaymentMethod input:checked").val();
      // Find credit-card input fields
      var ccInput = $("#PaymentMethod").nextAll('.credit-card');
      if (ccInput && ccInput.length > 0) {
        // Find gateway lookup data
        var lookup = ccInput.find(".gateway-lookup").data("gateways");
        if (lookup && (selected in lookup)) {
          // Show the Credit-Card fields if the gateway is in the lookup data
          ccInput.show();
          // Hide all CC fields by default
          ccInput.find(".field").hide();
          $(lookup[selected]).each(function (i, v) {
            // only show the required fields
            ccInput.find("[name=" + v + "]").parents(".field").show();
          });
        } else {
          ccInput.hide();
        }
      }
    };

    $("#PaymentMethod input").on("change", handleGatewayChanged);
    handleGatewayChanged();
  });
})(jQuery);
