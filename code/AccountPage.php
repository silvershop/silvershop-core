<?php
/**
 * @description: Account page shows order history and a form to allow the member to edit his/her details.
 *
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class AccountPage extends Page {

	public static $icon = 'ecommerce/images/icons/account';

	function canCreate() {
		return !DataObject::get_one("AccountPage");
	}

	/**
	 * Returns the link or the URLSegment to the account page on this site
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	public static function find_link($useURLSegment = false) {
		$page = self::get_if_account_page_exists();
		return ($useURLSegment) ? $page->URLSegment : $page->Link();
	}

	/**
	 * Return a link to view the order on the account page.
	 *
	 * @param int|string $orderID ID of the order
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	public static function get_order_link($orderID, $urlSegment = false) {
		$page = self::get_if_account_page_exists();
		return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . 'order/' . $orderID;
	}

	protected static function get_if_account_page_exists() {
		if($page = DataObject::get_one('AccountPage')) {
			return $page;
		}
		user_error('No AccountPage was found. Please create one in the CMS!', E_USER_ERROR);
	}

	/**
	 * Returns all {@link Order} records for this
	 * member that are incomplete.
	 *
	 * @return DataObjectSet
	 */
	function IncompleteOrders() {
		$statusFilter = "\"OrderStatus\".\"ShowAsUncompletedOrder\" = 1 ";
		return $this->OrderSQL($statusFilter);
	}

	/**
	 * Returns all {@link Order} records for this
	 * member that are completed.
	 *
	 * @return DataObjectSet
	 */
	function InProcessOrders() {
		$statusFilter = "\"OrderStatus\".\"ShowAsInProcessOrder\" = 1";
		return $this->OrderSQL($statusFilter);
	}

	/**
	 * Returns all {@link Order} records for this
	 * member that are completed.
	 *
	 * @return DataObjectSet
	 */
	function CompleteOrders() {
		$statusFilter = "\"OrderStatus\".\"ShowAsCompletedOrder\" = 1";
		return $this->OrderSQL($statusFilter);
	}

	protected function OrderSQL ($statusFilter) {
		$memberID = Member::currentUserID();
		if($memberID) {
			return DataObject::get(
				$className = 'Order',
				$where = "\"Order\".\"MemberID\" = '$memberID' AND $statusFilter",
				$sort = "\"Created\" DESC",
				$join = "INNER JOIN \"OrderStatus\" ON \"Order\".\"StatusID\" = \"OrderStatus\".\"ID\""
			);
		}
	}


	/**
	 * Automatically create an AccountPage if one is not found
	 * on the site at the time the database is built (dev/build).
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!DataObject::get_one('AccountPage')) {
			$page = new AccountPage();
			$page->Title = 'Account';
			$page->Content = '<p>This is the account page. It is used for shop users to login and change their member details if they have an account.</p>';
			$page->URLSegment = 'account';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Account page \'Account\' created', 'created');
		}
	}
}

class AccountPage_Controller extends Page_Controller {

	static $allowed_actions = array(
		'order',
		'CancelForm',
		'PaymentForm',
		'MemberForm'
	);

	function init() {
		parent::init();
		Requirements::themedCSS('AccountPage');
		if(!Member::currentUserID()) {
			$messages = array(
				'default' => '<p class="message good">' . _t('AccountPage.MESSAGE', 'You\'ll need to login before you can access the account page. If you are not registered, you won\'t be able to access it until you make your first order, otherwise please enter your details below.') . '</p>',
				'logInAgain' => _t('AccountPage.LOGINAGAIN', 'You have been logged out. If you would like to log in again, please do so below.')
			);
			Security::permissionFailure($this, $messages);
			return false;
		}
	}


	/**
	 * Return the {@link Order} details for the current
	 * Order ID that we're viewing (ID parameter in URL).
	 *
	 * @return array of template variables
	 */
	function order($request) {
		Requirements::themedCSS('Order');
		Requirements::themedCSS('Order_print', 'print');
		$accountPageLink = AccountPage::find_link();
		if($orderID = $request->param('ID')) {
			if($order = $this->getOrder()) {

				$paymentform = ($order->TotalOutstanding() > 0) ? $this->CancelForm() : null;

				return array(
					'Order' => $order,
					'Form' => $paymentform
				);
			}
			else {
				return array(
					'Order' => false,
					'Message' => 'You do not have any order corresponding to this ID. However, you can <a href="' . $accountPageLink . '">edit your own personal details and view your orders.</a>.'
				);
			}
		}
		else {
			return array(
				'Order' => false,
				'Message' => _t('AccountPage.ORDERNOTFOUND', 'Order can not be found.').' '._t('AccountPage.LINKTOACCOUNTPAGE', 'Go to '). '<a href="' . $accountPageLink . '">'.$this->Title.'</a>.'
			);
		}
	}

	/**
	 * Return a form allowing the user to edit
	 * their details with the shop.
	 *
	 * @return ShopAccountForm
	 */
	function MemberForm() {
		return new ShopAccountForm($this, 'MemberForm');
	}

	/**
	 * Returns the form to cancel the current order,
	 * checking to see if they can cancel their order
	 * first of all.
	 *
	 * @return Order_CancelForm
	 */
	function CancelForm() {
		$order = $this->getOrder();
		if($order && $order->canCancel()) {
			return new Order_CancelForm($this, 'CancelForm', $order->ID);
		}
		return null;
	}


	function PaymentForm(){
		$order = $this->getOrder();
		if($order && $form = new Order_CancelForm($this, 'PaymentForm', $order->ID)){
			$paymentFields = Payment::combined_form_fields($order->TotalOutstanding());
			$paymentFields->merge($form->Fields());
			$form->setFields($paymentFields);
			//TODO: add required fields
			$form->Actions()->push(new FormAction('payoutstanding', _t("AccountPage.PAYOUTSTANDING", "Pay Outstanding")));
			return $form;
		}
	}

	function payoutstanding($data,$form){
		$data = Convert::raw2sql($data);
		$order = $this->getOrder();
		return EcommercePayment::process_payment_form_and_return_next_step($order, $data, $form);
	}

	/**
	 * Gets the order, as specified in the url OrderID.
	 */
	protected function getOrder(){
		$memberID = Member::currentUserID();
		if($memberID) {
			$orderID = intval($this->getRequest()->param('ID'));
			if(!$orderID && isset($_POST['OrderID'])) {
				$orderID = intval($_POST['OrderID']);
			}
			if(is_numeric($orderID)) {
				return DataObject::get_one('Order', "\"Order\".\"ID\" = '$orderID' AND \"Order\".\"MemberID\" = '$memberID'");
			}
		}
		return null;
	}


}

