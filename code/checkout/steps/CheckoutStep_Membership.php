<?php

/**
 * Login, sign-up, or proceed as guest
 */
class CheckoutStep_Membership extends CheckoutStep{

	private static $allowed_actions = array(
		'membership',
		'MembershipForm',
		'LoginForm',
		'createaccount',
		'docreateaccount',
		'CreateAccountForm'
	);

	public static $skip_if_logged_in = true;

	public function membership() {
		//if logged in, then redirect to next step
		if(ShoppingCart::curr() && self::$skip_if_logged_in && Member::currentUser()){
			Controller::curr()->redirect($this->NextStepLink());
			return;
		}
		return $this->owner->customise(array(
			'Form' => $this->MembershipForm(),
			'LoginForm' => $this->LoginForm(),
			'GuestLink' => $this->NextStepLink()
		))->renderWith(array("CheckoutPage_membership","CheckoutPage","Page")); //needed to make rendering work on index
	}

	public function MembershipForm() {
		$fields = new FieldList();
		$actions = new FieldList(
			new FormAction("createaccount", "Create an Account"),
			new FormAction("guestcontinue", "Continue as Guest")
		);
		$form = new Form($this->owner, 'MembershipForm', $fields, $actions);
		$this->owner->extend('updateMembershipForm', $form);
		return $form;
	}

	public function guestcontinue() {
		$this->owner->redirect($this->NextStepLink());
	}

	public function LoginForm() {
		$form = new MemberLoginForm($this->owner, 'LoginForm');
		$this->owner->extend('updateLoginForm', $form);
		return $form;
	}

	public function createaccount($requestdata) {

		if(Member::currentUser()){ //we shouldn't create an account if already a member
			Controller::curr()->redirect($this->NextStepLink());
			return;
		}
		if(!($requestdata instanceof SS_HTTPRequest)){ //using this function to redirect, and display action
			Controller::curr()->redirect($this->NextStepLink('createaccount'));
			return;
		}
		return array(
			'Form' => $this->CreateAccountForm()
		);
	}

	public function registerconfig() {
		$config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
		$config->addComponent(new CustomerDetailsCheckoutComponent());
		$config->addComponent(new MembershipCheckoutComponent());

		return $config;
	}

	public function CreateAccountForm() {
		$form = new CheckoutForm($this->owner, "CreateAccountForm", $this->registerconfig());
		$form->setActions(new FieldList(
			new FormAction('docreateaccount', 'Create New Account')
		));
		$form->getValidator()->addRequiredField("Password");

		$this->owner->extend('updateCreateAccountForm', $form);
		return $form;
	}

	public function docreateaccount($data, Form $form) {
		$this->registerconfig()->setData($form->getData());

		return Controller::curr()->redirect($this->NextStepLink());
	}

}
