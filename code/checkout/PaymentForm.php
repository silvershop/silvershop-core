<?php

class PaymentForm extends CheckoutForm{

	protected $failurelink;

	public function setFailureLink($link) {
		$this->failurelink = $link;
	}

	public function checkoutSubmit($data, $form) {
		//form validation has passed by this point, so we can save data
		$this->config->setData($form->getData());
		$order = $this->config->getOrder();
		$gateway = Checkout::get($order)->getSelectedPaymentMethod(false);
		if(GatewayInfo::is_offsite($gateway) || GatewayInfo::is_manual($gateway)){

			return $this->submitpayment($data, $form);
		}

		return $this->controller->redirect(
			$this->controller->Link('payment') //assumes CheckoutPage
		);
	}

	public function submitpayment($data, $form) {
		$data = $form->getData();
		$data['cancelUrl'] = $this->failurelink ? $this->failurelink : $this->controller->Link();
		$order = $this->config->getOrder();
		$order->calculate();
		$processor = OrderProcessor::create($order);
		$response = $processor->makePayment(
			Checkout::get($order)->getSelectedPaymentMethod(false),
			$data
		);
		if($response){
			if($response->isRedirect() || $response->isSuccessful()){
				return $response->redirect();
			}
			$form->sessionMessage($response->getMessage(), 'bad');

		}else{
			$form->sessionMessage($processor->getError(), 'bad');
		}

		return $this->controller->redirectBack();
	}

}
