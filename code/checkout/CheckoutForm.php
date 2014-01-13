<?php

class CheckoutForm extends Form {
	
	protected $config;

	function __construct($controller, $name, CheckoutComponentConfig $config) {
		$this->config = $config;
		$fields = $config->getFormFields();

		$actions = new FieldList(
			FormAction::create(
				'checkoutSubmit',
				_t('CheckoutForm','Proceed to payment')
			)
		);
		$validator = new CheckoutComponentValidator($this->config);
		parent::__construct($controller, $name, $fields, $actions, $validator);
		$this->loadDataFrom($this->config->getData(), Form::MERGE_IGNORE_FALSEISH);
		if($sessiondata = Session::get("FormInfo.{$this->FormName()}.data")){
			$this->loadDataFrom($sessiondata, Form::MERGE_IGNORE_FALSEISH);
		}
	}

	function checkoutSubmit($data, $form) {
		//form validation has passed by this point, so we can save data
		$this->config->setData($form->getData());
		$order = $this->config->getOrder();
		$gateway = Checkout::get($order)->getSelectedPaymentMethod(false);
		if(GatewayInfo::is_offsite($gateway)){

			return $this->submitpayment($data, $form);
		}
		return $this->controller->redirect(
			$this->controller->Link('payment')
		);		
	}

	function submitpayment($data, $form, $request){
		$data = $form->getData();
		$data['cancelURL'] = $this->controller->Link();
		$order = $this->config->getOrder();
		$order->calculate();
		$processor = OrderProcessor::create($order);
		$response = $processor->makePayment(
			Checkout::get($order)->getSelectedPaymentMethod(false),
			$data
		);
		if($response){
			if($response->isRedirect()){
				return $this->controller->redirect($redirecturl);
			}
			if($response->isSuccessful()){
				return $response->redirect();
			}
			$form->sessionMessage($response->getMessage(),'bad');

		}else{
			$form->sessionMessage($processor->getError(),'bad');
		}

		return $this->controller->redirectBack();
	}

}
