<?php
 /**
  * ShopAccountForm allows shop members to update
  * their details with the shop.
  *
  * @package ecommerce
  */
class ShopAccountForm extends Form {

	function __construct($controller, $name) {
		$member = Member::currentUser();

		if($member && $member->exists()) {
			$fields = $member->getEcommerceFields();
			$passwordField = new ConfirmedPasswordField('Password', 'Password');

			if($member->Password != '') {
				$passwordField->setCanBeEmpty(true);
			}

			$fields->push(new LiteralField('LogoutNote', "<p class=\"message warning\">" . _t("MemberForm.LOGGEDIN","You are currently logged in as ") . $member->getName() . ". "._t('MemberForm.LOGGEDIN','Click <a href="Security/logout" title="Click here to log out">here</a> to log out.')."</p>"));
			$fields->push(new HeaderField(_t("MemberForm.LOGGEDINDETAILS","Login Details"), 3));
			$fields->push($passwordField);

			$requiredFields = new RequiredFields($member->getEcommerceRequiredFields());
		} else {
			$fields = new FieldSet();
		}

		$actions = new FieldSet(
			new FormAction('submit', _t("MemberForm.SAVECHANGES","Save Changes")),
			new FormAction('proceed', _t("MemberForm.SAVECHANGESANDPROCEEDTOCHECKOUT","Save Changes and Proceed to Checkout"))
		);

		parent::__construct($controller, $name, $fields, $actions, $requiredFields);

		if($member) $this->loadDataFrom($member);
	}

	/**
	 * Save the changes to the form
	 */
	function submit($data, $form, $request) {
		$member = Member::currentUser();
		if(!$member) return false;

		$form->saveInto($member);
		$member->write();
		$form->sessionMessage(_t("MemberForm.DETAILSSAVED",'Your details have been saved'), 'good');

		Director::redirectBack();
		return true;
	}

	/**
	 * Save the changes to the form, and redirect to the checkout page
	 */
	function proceed($data, $form, $request) {
		$member = Member::currentUser();
		if(!$member) return false;

		$form->saveInto($member);
		$member->write();
		$form->sessionMessage(_t("MemberForm.DETAILSSAVED",'Your details have been saved'), 'good');

		Director::redirect(CheckoutPage::find_link());
		return true;
	}

}
