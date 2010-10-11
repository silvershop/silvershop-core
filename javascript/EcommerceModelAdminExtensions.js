
;(function($) {
	$(document).ready(
		function() {
			EcommerceModelAdminExtensions.init();
		}
	);
})(jQuery);

var EcommerceModelAdminExtensions = {

	nextActionButtonSelector: "#action_goNext",

	prevActionButtonSelector: "#action_goPrev",

	nextRecordURLSelector: "#nextRecordURL",

	prevRecordURLSelector: "#prevRecordURL",

	areaForLoadingResults: "#right #ModelAdminPanel",

	goForwardButtonSelector: "#Form_EditForm_action_goForward, #Form_ResultsForm_action_goForward",

	goBackwardButtonSelector: "#Form_EditForm_action_goBack, #Form_ResultsForm_action_goBack",

	init: function () {
		jQuery(EcommerceModelAdminExtensions.nextActionButton).live(
			"click",
			function() {
				nextPage = jQuery(EcommerceModelAdminExtensions.nextRecordURLSelector).val();
				EcommerceModelAdminExtensions.loadForm(nextPage);
				return false;
			}
		);
		jQuery(EcommerceModelAdminExtensions.prevActionButtonSelector).live(
			"click",
			function() {
				prevPage = jQuery(EcommerceModelAdminExtensions.prevRecordURLSelector).val();
				EcommerceModelAdminExtensions.loadForm(prevPage);
				return false;
			}
		);
	},

	loadForm: function(url) {
		tinymce_removeAll();
		jQuery(EcommerceModelAdminExtensions.areaForLoadingResults).load(
			url,
			function(result) {
				if(typeof(successCallback) == "function") {
					successCallback.apply();
				}
				jQuery().hide(EcommerceModelAdminExtensions.goForwardButtonSelector);
				jQuery(EcommerceModelAdminExtensions.goBackwardButtonSelector).hide();

				Behaviour.apply(); // refreshes ComplexTableField
				if(window.onresize) {
					window.onresize();
				}
			}
		)
	}
}


