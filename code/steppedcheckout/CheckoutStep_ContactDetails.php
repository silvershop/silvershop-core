<?php

//TODO: require membership, or not

class CheckoutStep_ContactDetails extends CheckoutStep{
	
	static $allowed_actions = array(
		'contactdetails',
		'ContactDetailsForm'
	);
	
	function contactdetails(){
		$form = $this->ContactDetailsForm();
		$form->loadDataFrom(ShoppingCart::curr());
		return array(
			'Form' => $form
		);
	}
	
	function ContactDetailsForm(){
		$fields = CheckoutFieldFactory::singleton()->getContactFields();
		$actions = new FieldSet(
			new FormAction("setContactDetails","Continue")
		);
		
		//TODO: validation
		
		$form = new Form($this->owner, 'ContactDetailsForm', $fields, $actions);
		$this->owner->extend('updateForm',$form);
		return $form;
	}
	
	function setContactDetails($data,$form){
		if($order = ShoppingCart::curr()){
			$checkout = new Checkout($order);
			$checkout->setContactDetails($data['Email'],$data['FirstName'],$data['Surname']);
			Director::redirect($this->NextStepLink('shippingaddress'));
		}
		//TODO: fail / go somewhere
	}
	
}