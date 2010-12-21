/*
 *@author nicolaas[at]sunnysideup.co.nz
 *
 **/




;(function($) {
	$(document).ready(
		function() {
			OptionalConfirmedPasswordField.init();
		}
	);
})(jQuery);

var OptionalConfirmedPasswordField = {

	visible: "1",

	init: function() {
		jQuery(".showOnClick a").click(
			function() {
				jQuery(this).next(".showOnClickContainer").slideToggle();
				if(OptionalConfirmedPasswordField.visible) {
					OptionalConfirmedPasswordField.visible = "";
					}
				else {
					OptionalConfirmedPasswordField.visible = "1";
					jQuery(this).hide();
				}
				jQuery(this).next(".showOnClickContainer").children("input[type='hidden']").val(OptionalConfirmedPasswordField.visible);
				return false;
			}

		);
		jQuery(".showOnClick a").click();
		jQuery("#PasswordGroup input").attr("value", this.passwordgenerator(7, false));
	},

	passwordgenerator: function (length, special) {
		var iteration = 0;
		var password = "";
		var randomNumber;
		if(special == undefined){
				var special = false;
		}
		while(iteration < length){
			randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
			if(!special){
				if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
				if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
				if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
				if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
			}
			iteration++;
			password += String.fromCharCode(randomNumber);
		}
		return password;
	}

}


