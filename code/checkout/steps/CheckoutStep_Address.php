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
		if($shopuser = ShopUserInfo::get_location()){
			$form->loadDataFrom($shopuser->toMap());
		}
		if($member = Member::currentUser()){
			$form->loadDataFrom($member->DefaultShippingAddress()->toMap());
		}
		if(ShoppingCart::curr()->ShippingAddress()->exists()){
			$form->loadDataFrom(ShoppingCart::curr()->ShippingAddress());
		}
		$form->Fields()->push(
			new CheckboxField("SeperateBilling","Bill to a different address from this")
		);
		return array(
			'Form' => $form
		);	
	}
	
	function AddressForm(){
		$fields = singleton("Address")->getFrontEndFields();
		$actions = new FieldList(
			new FormAction("setaddress","Continue")
		);
		$validator =  new RequiredFields(singleton("Address")->getRequiredFields());
		$form = new Form($this->owner, 'AddressForm', $fields, $actions, $validator);
		$this->owner->extend('updateAddressForm',$form);
		return $form;
	}
	
	function billingaddress(){
		$form = $this->AddressForm();
		if($shopuser = ShopUserInfo::get_location()){
			$form->loadDataFrom($shopuser);
		}
		if($member = Member::currentUser()){
			$form->loadDataFrom($member->DefaultBillingAddress());
		}
		if(ShoppingCart::curr()->BillingAddress()->exists()){
			$form->loadDataFrom(ShoppingCart::curr()->BillingAddress());
		}
		$actions = new FieldList(
				new FormAction("setbillingaddress","Continue")
		);
		$form->setActions($actions);
		
		return array(
			'Form' => $form
		);
	}

	function setaddress($data,$form){
		$redirect = $this->NextStepLink();
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
		Controller::curr()->redirect($redirect);
	}
	
	function setbillingaddress($data,$form){
		if($order = ShoppingCart::curr()){
			$address = $this->addressFromForm($form);
			$checkout = new Checkout($order);
			$checkout->setBillingAddress($address);
			//TODO: either set new address, or choose matching existing member address
		}
		Controller::curr()->redirect($this->NextStepLink());
	}
	
	protected function addressFromForm($form){
		//TODO: either set new address, or choose matching existing member address
		$address = new Address();
		$form->saveInto($address);
		$address->write();
		return $address;
	}
	
}
