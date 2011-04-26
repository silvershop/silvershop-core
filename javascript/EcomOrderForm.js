/**
 *@author Nicolaas [at] sunnysideup.co.nz
 *
 *
 **/

;(function($) {
	$(document).ready(
		function() {
			EcomOrderForm.init();
		}
	);
	var EcomOrderForm = {

		chars : "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz",

		stringLength : 8,

		passwordFieldInputSelectors: "#PasswordGroup input",

		choosePasswordLinkSelector: ".choosePassword",

		passwordGroupHolderSelector: "#PasswordGroup",

		init: function() {
			this.passwordInitalisation();
		},

		//toggles password selection and enters random password so that users still end up with a password
		//even if they do not choose one.
		passwordInitalisation: function() {
			if(jQuery(EcomOrderForm.passwordFieldInputSelectors).length) {
				jQuery(EcomOrderForm.choosePasswordLinkSelector).click(
					function() {
						jQuery(EcomOrderForm.passwordGroupHolderSelector).toggle();
						if(jQuery(EcomOrderForm.passwordFieldInputSelectors).is(':visible')) {
							var newPassword = '';
						}
						else{
							var newPassword = EcomOrderForm.passwordGenerator();
						}
						$(EcomOrderForm.passwordFieldInputSelectors).val(newPassword);
						return false;
					}
				);
				jQuery(EcomOrderForm.choosePasswordLinkSelector).click();
			}
		},

		//generates random password
		passwordGenerator: function() {
			var randomstring = '';
			for (var i=0; i < this.stringLength; i++) {
				var rnum = Math.floor(Math.random() * this.chars.length);
				randomstring += this.chars.substring(rnum,rnum+1);
			}
			return randomstring;
		}
	}
})(jQuery);


