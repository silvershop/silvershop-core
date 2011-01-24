<?php
/**
 * @description: ShopAccountForm allows shop members to update their details with the shop.
 *
 * @see OrderModifier
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/
class ShopAccountForm extends Form {

	function __construct($controller, $name) {
		$member = Member::currentUser();
		$requiredFields = null;
		if($member && $member->exists()) {
			$fields = $member->getEcommerceFields();
			$passwordField = new ConfirmedPasswordField('Password', _t('ShopAccountForm.PASSWORD','Password'));
			if($member->Password != '') {
				$passwordField->setCanBeEmpty(true);
			}
			//TODO:is this necessary?
			$fields->push(new LiteralField('LogoutNote', "<p class=\"message warning\">" . _t("ShopAccountForm.LOGGEDIN","You are currently logged in as ") . $member->FirstName . ' ' . $member->Surname . ". "._t('ShopAccountForm.LOGOUT','Click <a href="Security/logout">here</a> to log out.')."</p>"));
			$fields->push(new HeaderField('Login Details',_t('ShopAccountForm.LOGINDETAILS','Login Details'), 3));
			$fields->push($passwordField);
			$requiredFields = new ShopAccountForm_Validator($member->getEcommerceRequiredFields());
		}
		else {
			$fields = new FieldSet();
		}
		if(get_class($controller) == 'AccountPage_Controller'){
			$actions = new FieldSet(new FormAction('submit', _t('ShopAccountForm.SAVE','Save Changes')));
		}
		else{
			$actions = new FieldSet(
				new FormAction('submit', _t('ShopAccountForm.SAVE','Save Changes')),
				new FormAction('proceed', _t('ShopAccountForm.SAVEANDPROCEED','Save and proceed to checkout'))
			);
		}
		if($record = $controller->data()){
			$record->extend('updateShopAccountForm',$fields,$actions,$requiredFields);
		}
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		if($member){
			$member->Password = ""; //prevents password field from being populated with encrypted password data
			$this->loadDataFrom($member);
		}
	}


	/**
	 * Save the changes to the form, and redirect to the checkout page
	 */
	function proceed($data, $form, $request) {
		return $this->processForm($data, $form, $request, CheckoutPage::find_link());
	}

	protected function processForm($data, $form, $request, $link = "") {
		$member = Member::currentUser();
		if(!$member) {
			return false;
		}
		$form->saveInto($member);
		$member->write();
		//TO DO: fix password....
		$form->sessionMessage(_t("ShopAccountForm.DETAILSSAVED",'Your details have been saved.'), 'good');
		if($link) {
			Director::redirect($link);
		}
		else {
			Director::redirectBack();
		}
		return true;
	}

}


class ShopAccountForm_Validator extends RequiredFields{

	/**
	 * Ensures member unique id stays unique and other basic stuff...
	 * TODO: check if this code is not part of Member itself, as it applies to any member form.
	 */
	function php($data){
		$valid = parent::php($data);
		$field = Member::get_unique_identifier_field();
		$currentMember = Member::currentUser();
		if(isset($data[$field])){
			$uid = $data[Member::get_unique_identifier_field()];
			//can't be taken
			if(DataObject::get_one('Member',"$field = '$uid' AND ID != ".$currentMember->ID)){
				$this->validationError(
					$field,
					"\"$uid\" "._t("ShopAccountForm.ALREADYTAKEN", " is already taken by another member. Please log in or use another \"$uid\"."),
					"required"
				);
				$valid = false;
			}
		}
		// check password fields are the same before saving
		if(isset($data["Password"]["_Password"]) && isset($data["Password"]["_ConfirmPassword"])) {
			if($data["Password"]["_Password"] != $data["Password"]["_ConfirmPassword"]) {
				$this->validationError(
					$field,
					_t("ShopAccountForm.PASSWORDSERROR", "Passwords do not match."),
					"required"
				);
				$valid = false;
			}
			if(!$currentMember && !$data["Password"]["_Password"]) {
				$this->validationError(
					$field,
					_t("ShopAccountForm.SELECTPASSWORD", "Please select a password."),
					"required"
				);
				$valid = false;
			}
			if($currentMember  && !$data["Password"]["_Password"]) {
				//TO DO: set password back to pre-existing password.
			}
		}
		return $valid;
	}

}
