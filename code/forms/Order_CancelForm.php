<?php
/**
 * @Description: allows customer to cancel order.
 *
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/


class Order_CancelForm extends Form {

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
		$SQLData = Convert::raw2sql($data);
		$member = $this->CurrentMember();
		if(isset($SQLData['OrderID']) && $order = DataObject::get_one('Order', "\"ID\" = ".$SQLData['OrderID']." AND \"MemberID\" = $member->ID")){
			if($order->canCancel()) {
				$order->CancelledByID = $member->ID;
				$order->write();
			}
			else {
				user_error("Tried to cancel an order that can not be cancelled with Order ID: ".$order->ID, "E_USER_NOTICE");
			}
		}
		//TODO: notify people via email??
		if($link = AccountPage::find_link()){
			//TODO: set session message "order successfully cancelled".
			Director::redirect($link);
		}
		else{
			Director::redirectBack();
		}
		return;
	}

}
