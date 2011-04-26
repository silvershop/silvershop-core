<?php
/**
 * @description: ShopAccountForm allows shop members to update their details with the shop.
 *
 * @see OrderModifier
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: forms
 *
 **/
class ShopAccountForm extends Form {

	function __construct($controller, $name) {
		$member = Member::currentUser();
		$requiredFields = null;
		if($member && $member->exists()) {
			$fields = $member->getEcommerceFields();
			$fields->push(new HeaderField('Login Details',_t('Account.LOGINDETAILS','Login Details'), 3));
			$logoutLink = ShoppingCart::clear_cart_and_logout_link();
			$fields->push(new LiteralField('LogoutNote', "<p class=\"message warning\">" . _t("Account.LOGGEDIN","You are currently logged in as ") . $member->FirstName . ' ' . $member->Surname . '. <a href="'.$logoutLink.'">'._t('Account.LOGOUT','Log out and clear your cart.')."</a></p>"));
			// PASSWORD KEPT CHANGING - SO I REMOVED IT FOR NOW - Nicolaas
			$passwordField = new ConfirmedPasswordField('Password', _t('Account.PASSWORD','Password'), "", null, true);
			$fields->push($passwordField);
			$requiredFields = new ShopAccountForm_Validator($member->getEcommerceRequiredFields());
		}
		else {
			$fields = new FieldSet();
		}
		$actions = new FieldSet(
			new FormAction('submit', _t('Account.SAVE','Save Changes'))
		);
		if($order = ShoppingCart::current_order()) {
			if($order->Items()) {
				$actions->push(new FormAction('proceed', _t('Account.SAVEANDPROCEED','Save changes and proceed to checkout')));
			}
		}
		if($record = $controller->data()){
			$record->extend('updateShopAccountForm',$fields,$actions,$requiredFields);
		}
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		if($member && $member->Password ){
			$this->loadDataFrom($member);
			if(!isset($_REQUEST["Password"])) {
				$this->fields()->fieldByName("Password")->SetValue("");
			}
			$this->fields()->fieldByName("Password")->setCanBeEmpty(true);
		}
	}


	/**
	 * Save the changes to the form, and go back to previous page.
	 */
	function submit($data, $form, $request) {
		return $this->processForm($data, $form, $request, "");
	}

	/**
	 * Save the changes to the form, and redirect to the checkout page
	 *@return ?????????????
	 */
	function proceed($data, $form, $request) {
		return $this->processForm($data, $form, $request, CheckoutPage::find_link());
	}

	/**
	 *@return Boolean
	 **/
	protected function processForm($data, $form, $request, $link = "") {
		$member = Member::currentUser();
		if(!$member) {
			$form->sessionMessage(_t('Account.DETAILSNOTSAVED','Your details could not be saved.'), 'bad');
			Director::redirectBack();
		}
		$form->saveInto($member);
		$member->write();

		if($link) {
			Director::redirect($link);
		}
		else {
			$form->sessionMessage(_t('Account.DETAILSSAVED','Your details have been saved.'), 'good');
			Director::redirectBack();
		}
		return true;
	}

}


class ShopAccountForm_Validator extends RequiredFields{

	/**
	 * Ensures member unique id stays unique and other basic stuff...
	 * @param $data = array Form Field Data
	 * @return Boolean
	 **/
	function php($data){
		$valid = parent::php($data);
		$field = Member::get_unique_identifier_field();
		$memberID = Member::currentUserID();
		if(isset($data[$field]) && $memberID && $data[$field]){
			$email = Convert::raw2sql($data[$field]);
			//can't be taken
			if(DataObject::get_one('Member',"\"$field\" = '$email' AND ID <> ".$memberID)){
				$this->validationError(
					$field,
					"\"$email\" "._t('Account.ALREADYTAKEN', " is already taken by another member. Please log in or use another \"$email\""),
					"required"
				);
				$valid = false;
			}
		}
		// check password fields are the same before saving
		if(isset($data["Password"]["_Password"]) && isset($data["Password"]["_ConfirmPassword"])) {
			if($data["Password"]["_Password"] != $data["Password"]["_ConfirmPassword"]) {
				$this->validationError(
					"Password",
					_t('Account.PASSWORDSERROR', 'Passwords do not match.'),
					"required"
				);
				$valid = false;
			}
			if(!$memberID && !$data["Password"]["_Password"]) {
				$this->validationError(
					"Password",
					_t('Account.SELECTPASSWORD', 'Please select a password.'),
					"required"
				);
				$valid = false;
			}
		}
		if(!$valid) {
			$this->form->sessionMessage(_t('Account.ERRORINFORM', 'We could not save your details, please check your errors below.'), "bad");
		}
		return $valid;
	}

}
