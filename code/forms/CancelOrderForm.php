<?php

/**
 * Form for canceling an order.
 * @package shop
 * @subpackage forms
 */
class Order_CancelForm extends Form {

	static $email_notification = false;

	function __construct($controller, $name, $orderID) {

		$fields = new FieldSet(
		new HiddenField('OrderID', '', $orderID)
		);
		$actions = new FieldSet(
		new FormAction('doCancel', _t('Order.CANCELORDER','Cancel this order'))
		);
		parent::__construct($controller, $name, $fields, $actions);
	}

	/**
	 * Form action handler for Order_CancelForm.
	 *
	 * Take the order that this was to be change on,
	 * and set the status that was requested from
	 * the form request data.
	 *
	 * @param array $data The form request data submitted
	 * @param Form $form The {@link Form} this was submitted on
	 */
	function doCancel($data, $form) {
		$SQL_data = Convert::raw2sql($data);
		$order = DataObject::get_by_id('Order', $SQL_data['OrderID']);
		$order->Status = 'MemberCancelled';
		$order->write();

		//TODO: notify people via email?? Make it optional.
		if(self::$email_notification){
			$email = new Email(Email::getAdminEmail(),Email::getAdminEmail(),sprintf(_t('Order.CANCELSUBJECT','Order #%d cancelled by member'),$order->ID),$order->renderWith('Order'));
			$email->send();
		}

		if(Member::currentUser() && $link = AccountPage::find_link()){
			//TODO: set session message "order successfully cancelled".
			Director::redirect($link); //TODO: can't redirect to account page when not logged in
		}else{

			$form->Controller()->setSessionMessage(_t("OrderForm.ORDERCANCELLED", "Order sucessfully cancelled"),'warning'); //assumes controller has OrderManipulation extension
			Director::redirectBack();
		}
		return;
	}

}