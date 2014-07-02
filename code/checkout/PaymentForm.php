<?php

class PaymentForm extends CheckoutForm{

	/**
	 * @var string URL to redirect the user to on payment success.
	 * Not the same as the "confirm" action in {@link PaymentGatewayController}.
	 */
	protected $successlink;

	/**
	 * @var string URL to redirect the user to on payment failure.
	 * Not the same as the "cancel" action in {@link PaymentGatewayController}.
	 */
	protected $failurelink;

	/**
	 * @var OrderProcessor
	 */
	protected $orderProcessor;

	public function __construct($controller, $name, CheckoutComponentConfig $config) {
		parent::__construct($controller, $name, $config);

		$this->orderProcessor = Injector::inst()->create('OrderProcessor', $config->getOrder());
	}

	public function setSuccessLink($link) {
		$this->successlink = $link;
	}

	public function getSuccessLink() {
		return $this->successlink;
	}

	public function setFailureLink($link) {
		$this->failurelink = $link;
	}

	public function getFailureLink() {
		return $this->failurelink;
	}

	public function checkoutSubmit($data, $form) {
		//form validation has passed by this point, so we can save data
		$this->config->setData($form->getData());
		$order = $this->config->getOrder();
		$gateway = Checkout::get($order)->getSelectedPaymentMethod(false);

		if (
			GatewayInfo::is_offsite($gateway) ||
			GatewayInfo::is_manual($gateway) ||
			$this->config->getComponentByType('OnsitePaymentCheckoutComponent')
		) {
			return $this->submitpayment($data, $form);
		}

		return $this->controller->redirect(
			$this->controller->Link('payment') //assumes CheckoutPage
		);
	}

	/**
	 * Behaviour can be overwritten by creating a processPaymentResponse method
	 * on the controller owning this form. It takes a Symfony\Component\HttpFoundation\Response argument,
	 * and expects an SS_HTTPResponse in return.
	 */
	public function submitpayment($data, $form) {
		$data = $form->getData();
		if($this->getSuccessLink()) {
			$data['returnUrl'] = $this->getSuccessLink();
		}
		$data['cancelUrl'] = $this->getFailureLink() ? $this->getFailureLink() : $this->controller->Link();
		$order = $this->config->getOrder();
		//final recalculation, before making payment
		$order->calculate();
		//handle cases where order total is 0. Note that the order will appear
		//as "paid", but without a Payment record attached.
		if($order->GrandTotal() == 0 && Order::config()->allow_zero_order_total){
			if($this->orderProcessor->placeOrder()){
				return $this->controller->redirect($this->getSuccessLink());
			}
			//TODO: store error for display?
			return $this->controller->redirectBack();
		}

		// if we got here from checkoutSubmit and there's a namespaced OnsitePaymentCheckoutComponent
		// in there, we need to strip the inputs down to only the checkout component.
		$components = $this->config->getComponents();
		if ($components->first() instanceof CheckoutComponent_Namespaced) {
			foreach ($components as $component) {
				if ($component->Proxy() instanceof OnsitePaymentCheckoutComponent) {
					$data = $component->unnamespaceData($data);
				}
			}
		}

		$paymentResponse = $this->orderProcessor->makePayment(
			Checkout::get($order)->getSelectedPaymentMethod(false),
			$data
		);

		$response = null;
		if($paymentResponse){
			if($this->controller->hasMethod('processPaymentResponse')) {
				$response = $this->controller->processPaymentResponse($paymentResponse, $form);
			} else if($paymentResponse->isRedirect() || $paymentResponse->isSuccessful()){
				$response = $paymentResponse->redirect();
			} else {
				$form->sessionMessage($paymentResponse->getMessage(), 'bad');
				$response = $this->controller->redirectBack();
			}
		} else {
			$form->sessionMessage($this->orderProcessor->getError(), 'bad');
			$response = $this->controller->redirectBack();
		}

		return $response;
	}

	/**
	 * @param OrderProcessor $processor
	 */
	public function setOrderProcessor(OrderProcessor $processor) {
		$this->orderProcessor = $processor;
	}

	/**
	 * @return OrderProcessor
	 */
	public function getOrderProcessor() {
		return $this->orderProcessor;
	}

}
