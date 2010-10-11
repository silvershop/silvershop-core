
;(function($) {
	$(document).ready(
		function() {
			EcommerceModelAdminExtensions.init();
		}
	);
})(jQuery);

var EcommerceModelAdminExtensions = {

	init: function () {
		jQuery('#action_goNext').live(
			'click',
			function() {
				nextPage = jQuery('#nextRecordURL').val();
				EcommerceModelAdminExtensions.loadForm(nextPage);
				return false;
			}
		);
		jQuery('#action_goPrev').live(
			'click',
			function() {
				prevPage = jQuery('#prevRecordURL').val();
				EcommerceModelAdminExtensions.loadForm(prevPage);
				return false;
			}
		);
	},

	loadForm: function(url) {
		tinymce_removeAll();
		jQuery('#right #ModelAdminPanel').load(
			url,
			function(result) {
				if(typeof(successCallback) == 'function') {
					successCallback.apply();
				}
				jQuery('#Form_EditForm_action_goForward, #Form_ResultsForm_action_goForward').hide();
				jQuery('#Form_EditForm_action_goBack, #Form_ResultsForm_action_goBack').hide();

				Behaviour.apply(); // refreshes ComplexTableField
				if(window.onresize) {
					window.onresize();
				}
			}
		)
	}
}


