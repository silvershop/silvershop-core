<?php
/**
 * Account page shows order history and a form to allow
 * the member to edit his/her details.
 *
 * @package ecommerce
 */
class AccountPage extends Page {

	static $add_action = 'an Account Page';

	static $icon = 'ecommerce/images/icons/account';

	static $db = array(

	);

	function canCreate() {
		return !DataObject::get_one("SiteTree", "\"ClassName\" = 'AccountPage'");
	}

	/**
	 * Returns the link or the URLSegment to the account page on this site
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	static function find_link($urlSegment = false) {
		$page = self::get_if_account_page_exists();
		return ($urlSegment) ? $page->URLSegment : $page->Link();
	}

	/**
	 * Return a link to view the order on the account page.
	 *
	 * @param int|string $orderID ID of the order
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	static function get_order_link($orderID, $urlSegment = false) {
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
	 * member that are completed.
	 *
	 * @return DataObjectSet
	 */
	function CompleteOrders() {
		$memberID = Member::currentUserID();
		$statusFilter = "\"Order\".\"Status\" IN ('" . implode("','", Order::$paid_status) . "')";
		$statusFilter .= " AND \"Order\".\"Status\" NOT IN('Cart')";
		return DataObject::get('Order', "\"Order\".\"MemberID\" = '$memberID' AND $statusFilter", "\"Created\" DESC");
	}

	/**
	 * Returns all {@link Order} records for this
	 * member that are incomplete.
	 *
	 * @return DataObjectSet
	 */
	function IncompleteOrders() {
		$memberID = Member::currentUserID();
		$statusFilter = "\"Order\".\"Status\" NOT IN ('" . implode("','", Order::$paid_status) . "')";
		$statusFilter .= " AND \"Order\".\"Status\" NOT IN('Cart')";
		return DataObject::get('Order', "\"Order\".\"MemberID\" = '$memberID' AND $statusFilter", "\"Created\" DESC");
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

	function init() {
		parent::init();

		Requirements::themedCSS('AccountPage');

		if(!Member::currentUserID()) {
			$messages = array(
				'default' => '<p class="message good">' . _t('AccountPage.Message', 'You\'ll need to login before you can access the account page. If you are not registered, you won\'t be able to access it until you make your first order, otherwise please enter your details below.') . '</p>',
				'logInAgain' => 'You have been logged out. If you would like to log in again, please do so below.'
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

		$memberID = Member::currentUserID();
		$accountPageLink = AccountPage::find_link();

		if($orderID = $request->param('ID')) {
			if($order = DataObject::get_one('Order', "\"Order\".\"ID\" = '$orderID' AND \"Order\".\"MemberID\" = '$memberID'")) {
				return array('Order' => $order);
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
				'Message' => 'There is no order by that ID. You can <a href="' . $accountPageLink . '">edit your own personal details and view your orders.</a>.'
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
		return null; // This needs to be fixed, URL routing is broken so ID doesn't get picked up

		if($order = DataObject::get_by_id('Order', (int) Director::urlParam('ID'))) {
			if($order->canCancel()) {
				return new Order_CancelForm($this, 'CancelForm', $order->ID);
			}
		}
	}

}

