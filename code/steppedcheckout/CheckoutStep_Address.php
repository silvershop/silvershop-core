<?php

class CheckoutStep_Address extends CheckoutStep{
	
	static $allowed_actions = array(
		'shippingaddress',
		'AddressForm',
		'setAddrress',
		'billingaddress',
		'BillingAddressForm',
		'setBillingAddress'
	);
	
	function shippingaddress(){
		$form = $this->AddressForm();
		$form->loadDataFrom(ShoppingCart::curr()->ShippingAddress());
		$form->Fields()->push(
			new CheckboxField("SeperateBilling","Bill to a different address from this")
		);
		return array(
			'Form' => $form
		);	
	}
	
	function AddressForm(){
		$fields = singleton("Address")->getFormFields("",true);
		$actions = new FieldSet(
			//new FormAction("useSeperateBillingAddress","Continue"), //TODO: add in billing address support
			new FormAction("setAddress","Continue")
		);
		$form = new Form($this->owner, 'AddressForm', $fields, $actions);
		$this->owner->extend('updateForm',$form);
		return $form;
	}
	
	function billingaddress(){
		$form = $this->AddressForm();
		$form->loadDataFrom(ShoppingCart::curr()->BillingAddress());
		$form->Actions()->emptyItems();
		$form->Actions()->push(
			new FormAction("setBillingAddress","Continue")
		);
		return array(
			'Form' => $form
		);
	}

	function setAddress($data,$form){
		$redirect = $this->NextStepLink('shippingmethod');
		if($order = ShoppingCart::curr()){
			$address = $this->addressFromForm($form);
			$checkout = new Checkout($order);
			$checkout->setShippingAddress($address);
			//TODO: either set new address, or choose matching existing member address
			if(isset($data['SeperateBilling']) && $data['SeperateBilling']){
				$redirect = $this->NextStepLink('billingaddress');
			}else{
				$checkout->setBillingAddress($address);
			}
		}
		Director::redirect($redirect);
	}
	
	function setBillingAddress($data,$form){
		if($order = ShoppingCart::curr()){
			$address = $this->addressFromForm($form);
			$checkout = new Checkout($order);
			$checkout->setBillingAddress($address);
			//TODO: either set new address, or choose matching existing member address
		}
		Director::redirect($this->NextStepLink('shippingmethod'));
	}
	
	protected function addressFromForm($form){
		//TODO: either set new address, or choose matching existing member address
		$address = new Address();
		$form->saveInto($address);
		$address->write();
		return $address;
	}
	
}