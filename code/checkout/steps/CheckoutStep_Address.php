<?php

class CheckoutStep_Address extends CheckoutStep{
		
	private static $allowed_actions = array(
		'shippingaddress',
		'ShippingAddressForm',
		'setshippingaddress',
		'billingaddress',
		'BillingAddressForm',
		'setbillingaddress'
	);

	function shippingconfig(){
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new ShippingAddressCheckoutComponent());

		return $config;
	}

	function shippingaddress(){
		$form = $this->ShippingAddressForm();
		$form->Fields()->push(new CheckboxField(
			"SeperateBilling","Bill to a different address from this"
		));
		return array('OrderForm' => $form);
	}

	function ShippingAddressForm(){
		$form = new CheckoutForm($this->owner, 'ShippingAddressForm', $this->shippingconfig());
		$form->setActions(new FieldList(
			new FormAction("setshippingaddress","Continue")
		));
		$this->owner->extend('updateAddressForm',$form);

		return $form;
	}
	
	function setshippingaddress($data, $form){
		$this->shippingconfig()->setData($form->getData());
		$step = null;
		if(isset($data['SeperateBilling']) && $data['SeperateBilling']){
			$step = "billingaddress";
		}
		return $this->owner->redirect($this->NextStepLink($step));
	}

	function billingconfig(){
		$config = new CheckoutComponentConfig(ShoppingCart::curr());
		$config->addComponent(new BillingAddressCheckoutComponent());

		return $config;
	}

	function billingaddress(){
		return array('OrderForm' => $this->BillingAddressForm());
	}

	function BillingAddressForm(){
		$form = new CheckoutForm($this->owner, 'BillingAddressForm', $this->billingconfig());
		$form->setActions(new FieldList(
			new FormAction("setbillingaddress","Continue")
		));
		$this->owner->extend('updateAddressForm',$form);

		return $form;
	}

	function setbillingaddress($data, $form){
		$this->billingconfig()->setData($form->getData());
		return $this->owner->redirect($this->NextStepLink($step));
	}

}
