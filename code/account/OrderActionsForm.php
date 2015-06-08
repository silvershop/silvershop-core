<?php

/**
 * Perform actions on placed orders
 *
 * @package shop
 * @subpackage forms
 */
class OrderActionsForm extends Form{

	private static $allowed_actions = array(
		'docancel',
		'dopayment',
		'httpsubmission'
	);

	private static $email_notification = false;
	private static $allow_paying = true;
	private static $allow_cancelling = true;

	protected $order;

	public function __construct($controller, $name, Order $order) {
		$this->order = $order;
		$fields = new FieldList(
			HiddenField::create('OrderID', '', $order->ID)
		);
		$actions = new FieldList();
		//payment
		if(self::config()->allow_paying && $order->canPay()){
			$gateways = GatewayInfo::get_supported_gateways();
			//remove manual gateways
			foreach($gateways as $gateway => $gatewayname){
				if(GatewayInfo::is_manual($gateway)){
					unset($gateways[$gateway]);
				}
			}
			if(!empty($gateways)){
				$fields->push(HeaderField::create("MakePaymentHeader",
					_t("OrderActionsForm.MAKEPAYMENT", "Make Payment"))
				);
				$outstandingfield = Currency::create();
				$outstandingfield->setValue($order->TotalOutstanding());
				$fields->push(LiteralField::create("Outstanding",
					sprintf(
						_t("OrderActionsForm.OUTSTANDING", "Outstanding: %s"),
						$outstandingfield->Nice()
					)
				));
				$fields->push(OptionsetField::create('PaymentMethod',
					_t("OrderActionsForm.PAYMENTMETHOD", "Payment Method"),
					$gateways,
					key($gateways)
				));

				$actions->push(FormAction::create('dopayment',
					_t('OrderActionsForm.PAYORDER', 'Pay outstanding balance')
				));
			}

		}
		//cancelling
		if(self::config()->allow_cancelling && $order->canCancel()){
			$actions->push(
				FormAction::create('docancel',
					_t('OrderActionsForm.CANCELORDER', 'Cancel this order')
				)
			);
		}
		parent::__construct($controller, $name, $fields, $actions);
		$this->extend("updateForm");
	}

	/**
	 * Make payment for a place order, where payment had previously failed.
	 *
	 * @param array $data
	 * @param Form $form
	 * @return boolean
	 */
	public function dopayment($data, $form) {
		if(self::config()->allow_paying &&
			$this->order &&
			$this->order->canPay()) {
			// Save payment data from form and process payment
			$data = $form->getData();
			$gateway = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;

			if(!GatewayInfo::is_manual($gateway)){
				$processor = OrderProcessor::create($this->order);
				$data['cancelUrl'] = $processor->getReturnUrl();
				$response = $processor->makePayment($gateway, $data);

				if($response){
					if($response->isRedirect() || $response->isSuccessful()){
						return $response->redirect();
					}
					$form->sessionMessage($response->getMessage(), 'bad');
				}else{
					$form->sessionMessage($processor->getError(), 'bad');
				}
			}else{
				$form->sessionMessage("Manual payment not allowed", 'bad');
			}

			return $this->controller->redirectBack();
		}
		$form->sessionMessage(
			_t('OrderForm.COULDNOTPROCESSPAYMENT', 'Payment could not be processed.'),
			'bad'
		);
		$this->controller->redirectBack();
	}

	/**
	 * Form action handler for CancelOrderForm.
	 *
	 * Take the order that this was to be change on,
	 * and set the status that was requested from
	 * the form request data.
	 *
	 * @param array $data The form request data submitted
	 * @param Form $form The {@link Form} this was submitted on
	 */
	public function docancel($data, $form) {
		if(self::config()->allow_cancelling &&
			$this->order->canCancel()){
			$this->order->Status = 'MemberCancelled';
			$this->order->write();
			if(self::config()->email_notification){
				$email = new Email(
					Email::config()->admin_email, Email::config()->admin_email,
					sprintf(
						_t('Order.CANCELSUBJECT', 'Order #%d cancelled by member'),
						$this->order->ID
					),
					$this->order->renderWith('Order')
				);
				$email->send();
			}
			$this->controller->sessionMessage(
				_t("OrderForm.ORDERCANCELLED", "Order sucessfully cancelled"),
				'warning'
			);
			if(Member::currentUser() && $link = $this->order->Link()){
				$this->controller->redirect($link);
			}else{
				$this->controller->redirectBack();
			}
		}

	}

}
