<?php
/**
 * @author Nicolaas [at] sunnysideup.co.nz
  *
  * @see CheckoutPage
  *
  * @package ecommerce
  **/
class OrderFormWithShippingAddress extends OrderForm {

	function __construct($controller, $name) {

		Requirements::javascript('ecommerce/javascript/OrderFormWithShippingAddress.js');

		parent::__construct($controller, $name);
		$order = ShoppingCart::current_order();
		$countryField = new DropdownField('ShippingCountry',  _t('OrderForm.COUNTRY','Country'), Geoip::getCountryDropDown(), EcommerceRole::find_country());
		$shippingFields = new CompositeField(
			new HeaderField(_t('OrderForm.SENDGOODSTODIFFERENTADDRESS','Send goods to different address'), 3),
			new LiteralField('ShippingNote', '<p class="message warning">'._t('OrderFormWithShippingAddress.SHIPPINGNOTE','Your goods will be sent to the address below.').'</p>'),
			new LiteralField('Help', '<p>'._t('OrderFormWithShippingAddress.HELP','You can use this for gift giving. No billing information will be disclosed to this address.').'</p>'),
			new TextField('ShippingName', _t('OrderFormWithShippingAddress.NAME','Name')),
			new TextField('ShippingAddress', _t('OrderFormWithShippingAddress.ADDRESS','Address')),
			new TextField('ShippingAddress2', _t('OrderFormWithShippingAddress.ADDRESS2','')),
			new TextField('ShippingCity', _t('OrderFormWithShippingAddress.CITY','City')),
			new TextField('ShippingPostalCode', _t('OrderFormWithShippingAddress.SHIPPINGPOSTALCODE','Postal Code')),
			$countryField
		);
		//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
		//$requiredFields[] = 'ShippingName';
		//$requiredFields[] = 'ShippingAddress';
		//$requiredFields[] = 'ShippingCity';
		//	$requiredFields[] = 'ShippingCountry';
		$shippingFields->SetID('ShippingFields');
		$shippingFields->setForm($this);
		$this->fields->insertBefore(new CheckboxField("UseShippingAddress", _t("", "Use Alternative Delivery Address"), $order->UseShippingAddress), "BottomOrder");
		$this->fields->insertBefore($shippingFields, "BottomOrder");
		$data = $this->getData();
		$this->loadDataFrom($data);
	}

	function processOrder($data, $form, $request) {
		return parent::processOrder($data, $form, $request);
	}

	/** Override form validation to make different shipping address button work */
	 function validate(){
		parent::validate(); //always validate on order processing
	 }

	function updateShippingCountry($data, $form, $request) {
		Session::set($this->FormName(), $data);
		ShoppingCart::set_country($data['Country']);
		//should we return JSON here?
		if(Director::is_ajax()){
			return ShoppingCart::return_data("success"); //return "success";
		}
		Director::redirectBack();
	}
}

