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
		$this->fields->addFieldToTab("",new CheckboxField("UseShippingAddress", "Use Alternative Delivery Address", $order->UseShippingAddress));
		$countryField = new DropdownField('ShippingCountry',  _t('OrderForm.Country','Country'), Geoip::getCountryDropDown(), EcommerceRole::find_country());
		$shippingFields = new CompositeField(
			new HeaderField(_t('OrderForm.SENDGOODSTODIFFERENTADDRESS','Send goods to different address'), 3),
			new LiteralField('ShippingNote', '<p class="message warning">'._t('OrderForm.SHIPPINGNOTE','Your goods will be sent to the address below.').'</p>'),
			new LiteralField('Help', '<p>'._t('OrderForm.HELP','You can use this for gift giving. No billing information will be disclosed to this address.').'</p>'),
			new TextField('ShippingName', _t('OrderForm.NAME','Name')),
			new TextField('ShippingAddress', _t('OrderForm.ADDRESS','Address')),
			new TextField('ShippingAddress2', _t('OrderForm.ADDRESS2','')),
			new TextField('ShippingCity', _t('OrderForm.CITY','City')),
			new TextField('ShippingPostalCode', _t('OrderForm.SHIPPINGPOSTALCODE','Postal Code')),
			$countryField
		);
		//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
		//$requiredFields[] = 'ShippingName';
		//$requiredFields[] = 'ShippingAddress';
		//$requiredFields[] = 'ShippingCity';
		//	$requiredFields[] = 'ShippingCountry';
		$shippingFields->SetID("ShippingFields");
		$this->fields->addFieldToTab("",$shippingFields);
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

