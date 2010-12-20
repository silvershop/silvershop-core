


;(function($) {
	$(document).ready(
		function() {
			OrderFormWithoutShippingAddress.init();
		}
	);
	var OrderFormWithoutShippingAddress = {

		chars : "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz",

		stringLength : 8,

		init: function() {
			this.passwordInitalisation();
		},

		passwordInitalisation: function() {
			if(!$("#Password_Password").val() || !$("#Password_ConfirmPassword").val() ) {
				var newPassword = this.passwordGenerator();
				$("#Password_Password").val(newPassword);
				$("#Password_ConfirmPassword").val(newPassword);
			}

		},

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


