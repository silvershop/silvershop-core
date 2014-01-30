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
	
	function __construct($controller, $name = "OrderActionsForm", Order $order) {
		$gateways = GatewayInfo::get_supported_gateways();
		$outstanding = new Money();
		$outstanding->setAmount($order->TotalOutstanding());
		$fields = new FieldList(
			new HiddenField('OrderID', '', $order->ID)
		);
		$actions = new FieldList();
		if(OrderActionsForm::config()->allow_paying && $order->canPay()){
			$actions->push(new FormAction('dopayment', _t('OrderActionsForm.PAYORDER','Pay outstanding balance')));
			$fields->push(new HeaderField("MakePaymentHeader",_t("OrderActionsForm.MAKEPAYMENT", "Make Payment")));
			$fields->push(new LiteralField("Outstanding",
				sprintf(_t("OrderActionsForm.OUTSTANDING","Outstanding: %s"),$outstanding->Nice())
			));
			$fields->push(
				new OptionsetField(
					'PaymentMethod',
					'Payment Method',
					$gateways,
					array_keys($gateways)
				)
			);
		}
		if(OrderActionsForm::config()->allow_cancelling && $order->canCancel()){
			$actions->push(new FormAction('docancel', _t('OrderActionsForm.CANCELORDER','Cancel this order')));
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
		if(OrderManipulation::$allow_paying && $order = $this->Controller()->orderfromid()) {
			//assumes that the controller is extended by OrderManipulation decorator
			if($order->canPay()) {
				// Save payment data from form and process payment
				$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
				$processor = OrderProcessor::create($order);
				$payment = $processor->createPayment($paymentClass);
				if(!$payment){
					$form->sessionMessage($processor->getError(), 'bad');
					Controller::curr()->redirect($order->Link());
					return;
				}
				$payment->ReturnURL = $order->Link(); //set payment return url
				$result = $payment->processPayment($data, $form);
				if($result->isProcessing()) {
					return $result->getValue();
				}
				if($result->isSuccess()) {
					$order->sendReceipt();
				}
				Controller::curr()->redirect($payment->ReturnURL);
				return;
			}
		}
		$form->sessionMessage(_t('OrderForm.COULDNOTPROCESSPAYMENT', 'Payment could not be processed.'),'bad');
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
		if(OrderManipulation::$allow_cancelling){
			$SQL_data = Convert::raw2sql($data);
			$order = DataObject::get_by_id('Order', $SQL_data['OrderID']);
			$order->Status = 'MemberCancelled';
			$order->write();
			//TODO: notify people via email?? Make it optional.
			if(self::config()->email_notification){
				$email = new Email(Email::getAdminEmail(),Email::getAdminEmail(),sprintf(_t('Order.CANCELSUBJECT','Order #%d cancelled by member'),$order->ID),$order->renderWith('Order'));
				$email->send();
			}
			$form->Controller()->setSessionMessage(_t("OrderForm.ORDERCANCELLED", "Order sucessfully cancelled"),'warning'); //assumes controller has OrderManipulation extension
			if(Member::currentUser() && $link = $order->Link()){
				Controller::curr()->redirect($link);
			}else{
				Controller::curr()->redirectBack();
			}
		}

	}
	
}
