<?php
/**
 * Account page shows order history and a form to allow
 * the member to edit his/her details.
 *
 * @package shop
 */
class AccountPage extends Page {

	private static $icon = 'shop/images/icons/account';

	public function canCreate($member = null) {
		return !self::get()->exists();
	}

	/**
	 * Returns the link or the URLSegment to the account page on this site
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	public static function find_link($urlSegment = false) {
		$page = self::get_if_account_page_exists();
		return ($urlSegment) ? $page->URLSegment : $page->Link();
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

}

class AccountPage_Controller extends Page_Controller {

	private static $url_segment = 'account';
	private static $allowed_actions = array(
		'addressbook',
		'CreateAddressForm',
		'DefaultAddressForm',
		'editprofile',
		'EditAccountForm',
		'ChangePasswordForm'
	);

	protected $member;

	public function init() {
		parent::init();
		if(!Member::currentUserID()) {
			$messages = array(
				'default' => _t(
					'AccountPage.LOGIN',
					'You\'ll need to login before you can access the account page.
					If you are not registered, you won\'t be able to access it until
					you make your first order, otherwise please enter your details below.'),
				'logInAgain' => _t(
					'AccountPage.LOGINAGAIN',
					'You have been logged out. If you would like to log in again,
					please do so below.')
			);
			Security::permissionFailure($this, $messages);
			return false;
		}
		$this->member = Member::currentUser();
	}

	public function getTitle() {
		if($this->dataRecord && $title = $this->dataRecord->Title){
			return $title;
		}
		return _t('AccountPage.Title', "Account");
	}

	public function getMember() {
		return $this->member;
	}

	public function addressbook() {
		return array(
			'DefaultAddressForm' => $this->DefaultAddressForm(),
			'CreateAddressForm' => $this->CreateAddressForm()
		);
	}

	public function DefaultAddressForm() {
		$addresses = $this->member->AddressBook()->sort('Created', 'DESC');
		if($addresses->exists()){
			$fields = new FieldList(
				DropdownField::create(
					"DefaultShippingAddressID",
                    _t("Address.ShippingAddress", "Shipping Address"),
					$addresses->map('ID', 'toString')->toArray()
				),
				DropdownField::create(
					"DefaultBillingAddressID",
                    _t("Address.BillingAddress", "Billing Address"),
					$addresses->map('ID', 'toString')->toArray()
				)
			);
			$actions = new FieldList(
                new FormAction("savedefaultaddresses", _t("Address.SaveDefaults", "Save Defaults"))
			);
			$form = new Form($this, "DefaultAddressForm", $fields, $actions);
			$form->loadDataFrom($this->member);

			return $form;
		}

		return false;
	}

	public function savedefaultaddresses($data,$form) {
		$form->saveInto($this->member);
		$this->member->write();
		$this->redirect($this->Link('addressbook'));
	}

	public function CreateAddressForm() {
		$singletonaddress = singleton('Address');
		$fields = $singletonaddress->getFrontEndFields();
		$actions = new FieldList(
            new FormAction("saveaddress", _t("Address.SaveNew", "Save New Address"))
		);
		$validator = new RequiredFields($singletonaddress->getRequiredFields());

		return new Form($this, "CreateAddressForm", $fields, $actions, $validator);
	}

	public function saveaddress($data,$form) {
		$member = $this->getMember();
		$address = new Address();
		$form->saveInto($address);
		$address->MemberID = $member->ID;
		$address->write();
		if(!$member->DefaultShippingAddressID){
			$member->DefaultShippingAddressID = $address->ID;
			$member->write();
		}
		if(!$member->DefaultBillingAddressID){
			$member->DefaultBillingAddressID = $address->ID;
			$member->write();
		}
		$this->redirect($this->Link('addressbook'));
	}

	public function editprofile() {
		return array();
	}

	/**
	 * Return a form allowing the user to edit their details.
	 *
	 * @return ShopAccountForm
	 */
	public function EditAccountForm() {
		return new ShopAccountForm($this, 'EditAccountForm');
	}

	public function ChangePasswordForm() {
		return new ChangePasswordForm($this, "ChangePasswordForm");
	}

}
