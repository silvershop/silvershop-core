<?php

class CustomerDetailsCheckoutComponent extends CheckoutComponent{

	protected $requiredfields = array(
		'FirstName','Surname','Email'
	);

	public function getFormFields(Order $order){
		$fields = new FieldList(
			$firstname = TextField::create('FirstName', _t('CheckoutField.FIRSTNAME','First Name')),
			$surname = TextField::create('Surname', _t('CheckoutField.SURNAME','Surname')),
			$email = EmailField::create('Email', _t('CheckoutField.EMAIL','Email'))
		);
		//populate fields with member details, if logged in
		if($member = Member::currentUser()){
			$firstname->setValue($member->FirstName);
			$surname->setValue($member->Surname);
			$email->setValue($member->Email);
		}

		return $fields;
	}
		
	public function validateData(Order $order, array $data){
		//all fields are required
	}

	public function getData(Order $order){
		return array(
			'FirstName' => $order->FirstName,
			'Surname' => $order->Surname,
			'Email' => $order->Email
		);
	}

	public function setData(Order $order, array $data){
		$order->update($data);
		$order->write();
	}
	
}