/**
*@author nicolaas[at]sunnysideup . co . nz
*
**/

(function($){

	$(document).ready(
		function() {
			ShopManagerPage.init();
		}
	);


})(jQuery);


var ShopManagerPage = {

	linkSelectors: "#ShopManagerPageOptionList a",

	inputSelector: "#ShopManagerPageOrderID",

	orderListSelector: "#ShopManagerPageLastOrders",

	showHideNextSelector: "p.showHideNext a",

	init: function () {
		jQuery(ShopManagerPage.linkSelectors).click(
			function() {
				var val = parseInt(jQuery(ShopManagerPage.inputSelector).val());
				if(val) {
					jQuery(this).attr("href", jQuery(this).attr("href") + "/" + val);
					return true;
				}
				else {
					alert("please enter an order number first");
					jQuery(ShopManagerPage.inputSelector).focus();
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


