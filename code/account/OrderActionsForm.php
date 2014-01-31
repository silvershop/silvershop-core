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
	
	function __construct($controller, $name = "OrderActionsForm", Order $order) {
		$this->order = $order;

		$fields = new FieldList(
			HiddenField::create('OrderID', '', $order->ID)
		);
		$actions = new FieldList();
		if(OrderActionsForm::config()->allow_paying && $order->canPay()){
			$actions->push(FormAction::create('dopayment',
				_t('OrderActionsForm.PAYORDER','Pay outstanding balance')
			));
			$fields->push(HeaderField::create("MakePaymentHeader",
				_t("OrderActionsForm.MAKEPAYMENT", "Make Payment"))
			);
			$outstandingfield = Currency::create();
			$outstandingfield->setValue($order->TotalOutstanding());
			$fields->push(LiteralField::create("Outstanding",
				sprintf(
					_t("OrderActionsForm.OUTSTANDING","Outstanding: %s"),
					$outstandingfield->Nice()
				)
			));
			$gateways = GatewayInfo::get_supported_gateways();
			$fields->push(OptionsetField::create(
				'PaymentMethod','Payment Method',$gateways,key($gateways)
			));
		}
		if(OrderActionsForm::config()->allow_cancelling && $order->canCancel()){
			$actions->push(
				FormAction::create('docancel', 
					_t('OrderActionsForm.CANCELORDER','Cancel this order')
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
	function dopayment($data, $form) {
		if(OrderActionsForm::config()->allow_paying && $this->order) {
			//assumes that the controller is extended by OrderManipulation decorator
			if($this->order->canPay()) {
				// Save payment data from form and process payment
				$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
				$processor = OrderProcessor::create($this->order);
				$payment = $processor->createPayment($paymentClass);
				if(!$payment){
					$form->sessionMessage($processor->getError(), 'bad');
					Controller::curr()->redirect($this->order->Link());
					return;
				}
				$payment->ReturnURL = $this->order->Link(); //set payment return url
				$result = $payment->processPayment($data, $form);
				if($result->isProcessing()) {
					return $result->getValue();
				}
				if($result->isSuccess()) {
					$this->order->sendReceipt();
				}
				Controller::curr()->redirect($payment->ReturnURL);
				return;
			}
		}
		$form->sessionMessage(
			_t('OrderForm.COULDNOTPROCESSPAYMENT', 'Payment could not be processed.'),
			'bad'
		);
		Controller::curr()->redirectBack();
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
	function docancel($data, $form) {
		if(OrderActionsForm::config()->allow_cancelling &&
			$this->order->canCancel()){
			$this->order->Status = 'MemberCancelled';
			$this->order->write();
			if(self::config()->email_notification){
				$email = new Email(
					Email::getAdminEmail(),Email::getAdminEmail(),
					sprintf(
						_t('Order.CANCELSUBJECT','Order #%d cancelled by member'),
						$order->ID
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
				Controller::curr()->redirect($link);
			}else{
				Controller::curr()->redirectBack();
			}
		}

	}
	
}