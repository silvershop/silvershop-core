/**
*@author nicolaas[at]sunnysideup . co . nz
*
**/

(function($){

	$(document).ready(
		function() {
			EcomShopManagerPage .init();
		}
	);


})(jQuery);


var EcomShopManagerPage  = {

	linkSelectors: "#EcomShopManagerPage OptionList a",

	inputSelector: "#EcomShopManagerPage OrderID",

	orderListSelector: "#EcomShopManagerPage LastOrders",

	showHideNextSelector: "p.showHideNext a",

	init: function () {
		jQuery(EcomShopManagerPage .linkSelectors).click(
			function() {
				var val = parseInt(jQuery(EcomShopManagerPage .inputSelector).val());
				if(val) {
					jQuery(this).attr("href", jQuery(this).attr("href") + "/" + val);
					return true;
				}
				else {
					alert("please enter an order number first");
					jQuery(EcomShopManagerPage .inputSelector).focus();
					return false;
				}

			}
		);
		jQuery(this.showHideNextSelector).click(
			function() {
				jQuery(this).parent().next().slideToggle();
			}
		);
		jQuery(this.showHideNextSelector).click();
	}

}


