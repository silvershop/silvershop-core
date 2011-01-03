


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
			if(jQuery("#PasswordGroup input").length) {
				var newPassword = this.passwordGenerator();
				$("#PasswordGroup input").val(newPassword);
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


