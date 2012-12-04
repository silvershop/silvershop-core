<?php

class CheckoutStep_ContactDetails extends CheckoutStep{
	
	static $allowed_actions = array(
		'contactdetails',
		'ContactDetailsForm'
	);
	
	function contactdetails(){
		$form = $this->ContactDetailsForm();
		if($member = Member::currentUser()){
			$form->loadDataFrom($member);
		}
		if($cart = ShoppingCart::curr()){
			$form->loadDataFrom($cart->toMap()); //converting cart to array means it wont overwrite with empty data
		}
		return array(
			'Form' => $form
		);
	}
	
	function ContactDetailsForm(){
		$fields = CheckoutFieldFactory::singleton()->getContactFields();
		$actions = new FieldSet(
			new FormAction("setcontactdetails","Continue")
		);
		$validator =  new RequiredFields(array_keys($fields->dataFields())); //require all fields
		$form = new Form($this->owner, 'ContactDetailsForm', $fields, $actions,$validator);
		$this->owner->extend('updateContactDetailsForm',$form);
		return $form;
	}
	
	function setcontactdetails($data,$form){
		if($order = ShoppingCart::curr()){
			$checkout = new Checkout($order);
			$checkout->setContactDetails($data['Email'],$data['FirstName'],$data['Surname']);
			Director::redirect($this->NextStepLink());
			return;
		}
		Director::redirect($this->owner->Link());
	}
	
}