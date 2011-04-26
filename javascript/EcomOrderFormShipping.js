/**
 *@author nicolaas[at]sunnysideup.co.nz
 * This adds functionality to the shipping address section of the checkout form
 *
 **/

;(function($) {
	$(document).ready(
		function() {
			EcomOrderFormWithShippingAddress.init();
			EcomOrderFormWithShippingAddress.removeEmailFromShippingCityHack();
		}
	);
	var EcomOrderFormWithShippingAddress = {

		firstNameSelector: "#FirstName input"

		shippingFirstNameSelector: "#ShippingFirstName input",

		surnameSelector: "#Surname input",

		shippingSurnameSelector: "#ShippingSurname input",

		addressSelector: "#Address input",

		extraAddressSelector: "#Address2 input",

		shippingAddressSelector: "#ShippingAddress input",

		shippingExtraAddressSelector: "#ShippingAddress2 input",

		citySelector: "#City input",

		shippingCitySelector: "#ShippingCity input",

		postalCodeSelector: "#PostalCode input",

		shippingPostalCode: "#ShippingPostalCode input",

		countrySelector: "#Country select",

		shippingCountrySelector: "#ShippingCountry select",

		shippingSectionSelector: "#ShippingFields",

		useShippingDetailsSelector: "input[name='UseShippingAddress']",

		//hides shipping fields
		//toggle shipping fields when "use separate shipping address" is ticked
		//update shipping fields, when billing fields are changed.
		init: function(){
			//hide shipping fields
			jQuery(EcomOrderFormWithShippingAddress.shippingSectionSelector).hide();
			//turn-on shipping details toggle
			jQuery(EcomOrderFormWithShippingAddress.useShippingDetailsSelector).change(
				function(){
					jQuery(EcomOrderFormWithShippingAddress.shippingSectionSelector).slideToggle();
					jQuery(EcomOrderFormWithShippingAddress.shippingNameSelector).focus();
					EcomOrderFormWithShippingAddress.updateFields();
				}
			);
			//update on change
			var originatorFieldSelector =
					EcomOrderFormWithShippingAddress.firstNameSelector+", "+
					EcomOrderFormWithShippingAddress.surnameSelector+", "+
					EcomOrderFormWithShippingAddress.addressSelector+" ,"+
					EcomOrderFormWithShippingAddress.extraAddressSelector+", "+
					EcomOrderFormWithShippingAddress.citySelector+", "+
					EcomOrderFormWithShippingAddress.postalCodeSelector;
			jQuery(originatorFieldSelector).change(
				function() {
					EcomOrderFormWithShippingAddress.updateFields();
				}
			).focus(
				function() {
					EcomOrderFormWithShippingAddress.updateFields();
				}
			);
			if(jQuery(EcomOrderFormWithShippingAddress.useShippingDetailsSelector).is(":checked")) {
				jQuery(EcomOrderFormWithShippingAddress.shippingSectionSelector).slideToggle();
				jQuery(EcomOrderFormWithShippingAddress.shippingNameSelector).focus();
				EcomOrderFormWithShippingAddress.updateFields();
			}
		},

		//copy the billing address details to the shipping address details
		updateFields: function() {
			//postal code
			var PostalCode = jQuery(EcomOrderFormWithShippingAddress.postalCodeSelector).val();
			var ShippingPostalCode = jQuery(EcomOrderFormWithShippingAddress.shippingPostalCode).val();
			if(!ShippingPostalCode && PostalCode) {
				jQuery(EcomOrderFormWithShippingAddress.shippingPostalCode).val(PostalCode);
			}

			//country
			var Country = jQuery(EcomOrderFormWithShippingAddress.countrySelector).val();
			var ShippingCountry = jQuery(EcomOrderFormWithShippingAddress.shippingCountrySelector).val();
			if((!ShippingCountry || ShippingCountry == "AF") && Country) {
				jQuery(EcomOrderFormWithShippingAddress.shippingCountrySelector).val(Country);
			}

			//city
			var City = jQuery(EcomOrderFormWithShippingAddress.citySelector).val();
			var ShippingCity = jQuery(EcomOrderFormWithShippingAddress.shippingCitySelector).val();
			if(!ShippingCity && City) {
				jQuery(EcomOrderFormWithShippingAddress.shippingCitySelector).val(City);
			}
			//address
			var Address = jQuery(EcomOrderFormWithShippingAddress.addressSelector).val();
			var ShippingAddress = jQuery(EcomOrderFormWithShippingAddress.shippingAddressSelector).val();
			if(!ShippingAddress && Address) {
				jQuery(EcomOrderFormWithShippingAddress.shippingAddressSelector).val(Address);
			}
			//address 2
			var AddressLine2 = jQuery(EcomOrderFormWithShippingAddress.extraAddressSelector).val();
			var ShippingAddress2 = jQuery(EcomOrderFormWithShippingAddress.shippingExtraAddressSelector).val();
			if(!ShippingAddress2 && AddressLine2) {
				jQuery(EcomOrderFormWithShippingAddress.shippingExtraAddressSelector).val(AddressLine2);
			}
			//name
			var FirstName = jQuery(EcomOrderFormWithShippingAddress.firstNameSelector).val();
			var ShippingFirstName = jQuery(EcomOrderFormWithShippingAddress.shippingFirstNameSelector).val();
			if(!ShippingFirstName ||  && FirstName) {
				jQuery(EcomOrderFormWithShippingAddress.shippingFirstNameSelector).val(FirstName);
			}
			var Surname = jQuery(EcomOrderFormWithShippingAddress.SurnameSelector).val();
			var ShippingSurname = jQuery(EcomOrderFormWithShippingAddress.shippingSurnameSelector).val();
			if(!ShippingSurname ||  && Surname) {
				jQuery(EcomOrderFormWithShippingAddress.shippingSurnameSelector).val(Surname);
			}

		},

		//this function exists, because FF was auto-completing Shipping City as the username part of a password / username combination (password being the next field)
		removeEmailFromShippingCityHack: function() {
			var pattern=/^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/;
			var shippingCitySelectorValue = jQuery(EcomOrderFormWithShippingAddress.shippingCitySelector).val();
			if(pattern.test(shippingCitySelectorValue)){
				jQuery(EcomOrderFormWithShippingAddress.shippingCitySelector).val(jQuery(EcomOrderFormWithShippingAddress.citySelector).val());
			}
			else{
				//do nothing
			}

		}
	}
})(jQuery);


