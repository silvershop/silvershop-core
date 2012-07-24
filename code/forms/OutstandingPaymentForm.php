<?php

/**
* Form for paying outstanding orders.
* @package shop
* @subpackage forms
*/
class OutstandingPaymentForm extends Form {

	function __construct($controller, $name, $order) {
		$fields = new FieldSet(
		new HiddenField('OrderID', '', $order->ID)
		);
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
		$actions = new FieldSet(
		new FormAction('dopayment', _t('OrderForm.PAYORDER','Pay outstanding balance'))
		);
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		$this->extend('updateForm');
	}

	function dopayment($data, $form) {
		if($order = $this->Controller()->orderfromid()) {
			//assumes that the controller is extended by OrderManipulation decorator
			if($order->canPay()) {

				//TODO: move this to $order->makepayment($amount,$data);

				$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
				$payment = class_exists($paymentClass) ? new $paymentClass() : null;

				if(!($payment && $payment instanceof Payment)) {
					user_error(get_class($payment) . ' is not a valid Payment object!', E_USER_ERROR);
				}

				$form->saveInto($payment);
				$payment->OrderID = $order->ID;
				$payment->PaidForID = $order->ID;
				$payment->PaidForClass = $order->class;

				$payment->Amount->Amount = $order->Total();
				$payment->write();

				$result = $payment->processPayment($data, $form);

				// isProcessing(): Long payment process redirected to another website (PayPal, Worldpay)
				if($result->isProcessing()) {
					return $result->getValue();
				}

				if($result->isSuccess()) {
					$order->sendReceipt();
				}

				Director::redirect($order->Link());
				return true;
			}
		}
		$form->sessionMessage(_t(
			'OrderForm.COULDNOTPROCESSPAYMENT',
			'Payment could not be processed.'
		),'bad');
		Director::redirectBack();
		return false;
	}

}