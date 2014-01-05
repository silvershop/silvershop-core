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

		if(!$order->canPay()){
			//TODO: allow $0 orders
				//process order
			$this->controller->redirectBack();
			return;
		}

		if(GatewayInfo::is_offsite($gateway)){
			$redirecturl = OrderProcessor::create($order)->makePayment(
				Checkout::get($order)->getSelectedPaymentMethod(false),
				$form->getData()
			);
			//TODO: handle cancel or gateway failures
			$this->controller->redirect($redirecturl);
			return;
		}

		$this->controller->redirect(
			$this->controller->Link('payment')
		);		
	}

}
