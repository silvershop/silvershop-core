<?php

/**
 * Perform actions on placed orders
 *
 * @package shop
 * @subpackage forms
 */
class OrderActionsForm extends Form{
	
	static $email_notification = false;
	
	static $allowed_actions = array(
		'docancel',
		'dopayment',
		'httpsubmission'
	);
	
	function handleRequest($request){
		return parent::handleRequest($request);
	}
	
	function __construct($controller, $name = "OrderActionsForm", Order $order) {
		$fields = new FieldSet(
			new HiddenField('OrderID', '', $order->ID)
		);
		$actions = new FieldSet();
		
		if(OrderManipulation::$allow_paying && $order->canPay() && $order->canCancel()){
			$actions->push(new FormAction('dopayment', _t('OrderActionsForm.PAYORDER','Pay outstanding balance')));
			$totalAsCurrencyObject = DBField::create('Currency',$order->TotalOutstanding()); //This should really be handled by the payment module
			$paymentFields = Payment::combined_form_fields($totalAsCurrencyObject->Nice());
			foreach($paymentFields as $paymentField) {
				if($paymentField->class == "HeaderField") {
					$paymentField->setTitle(_t("OrderForm.MAKEPAYMENT", "Make Payment"));
				}
				$fields->push($paymentField);
			}
			$requiredFields = array();
			if($paymentRequiredFields = Payment::combined_form_requirements()) {
				$requiredFields = array_merge($requiredFields, $paymentRequiredFields);
			}
		}
		
		if(OrderManipulation::$allow_cancelling && $order->canCancel()){
			$actions->push(new FormAction('docancel', _t('OrderActionsForm.CANCELORDER','Cancel this order')));
		}
		
		parent::__construct($controller, $name, $fields, $actions);
		$this->extend("updateForm");
	}
	
	/**
	 * Make payment for a place order, where payment had previously failed.
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $form
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
					Director::redirect($order->Link());
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
				Director::redirect($payment->ReturnURL);
				return;
			}
		}
		$form->sessionMessage(_t('OrderForm.COULDNOTPROCESSPAYMENT', 'Payment could not be processed.'),'bad');
		Director::redirectBack();
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
			if(self::$email_notification){
				$email = new Email(Email::getAdminEmail(),Email::getAdminEmail(),sprintf(_t('Order.CANCELSUBJECT','Order #%d cancelled by member'),$order->ID),$order->renderWith('Order'));
				$email->send();
			}
			$form->Controller()->setSessionMessage(_t("OrderForm.ORDERCANCELLED", "Order sucessfully cancelled"),'warning'); //assumes controller has OrderManipulation extension
			if(Member::currentUser() && $link = $order->Link()){
				Director::redirect($link);
			}else{
				Director::redirectBack();
			}
		}

	}
	
}

/**
 * Form for paying outstanding orders.
 * @package shop
 * @subpackage forms
 *
 * @deprecated use OrderActionsForm
 */
class OutstandingPaymentForm extends OrderActionsForm {}

/**
 * Form for canceling an order.
 * @package shop
 * @subpackage forms
 * 
 * @deprecated use OrderActionsForm
 */
class CancelOrderForm extends Form {}
