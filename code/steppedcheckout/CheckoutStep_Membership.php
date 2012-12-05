<?php

/**
 * Login, sign-up, or proceed as guest
 */
class CheckoutStep_Membership extends CheckoutStep{
	
	static $allowed_actions = array(
		'membership',
		'MembershipForm',
		'LoginForm',
		'createaccount',
		'docreateaccount',
		'CreateAccountForm'
	);
	
	static $skip_if_logged_in = true;
	
	function membership(){
		//if logged in, then redirect to next step
		if(ShoppingCart::curr() && self::$skip_if_logged_in && Member::currentUser()){
			Director::redirect($this->NextStepLink());
			return;
		}
		return $this->owner->customise(array(
			'Form' => $this->MembershipForm(),
			'LoginForm' => $this->LoginForm(),
			'GuestLink' => $this->NextStepLink()
		))->renderWith(array("CheckoutPage_membership","CheckoutPage","Page")); //needed to make rendering work on index
	}
	
	function MembershipForm(){
		$fields = new FieldSet();
		$actions = new FieldSet(
			new FormAction("createaccount","Create an Account"),
			new FormAction("guestcontinue","Continue as Guest")
		);
		$form = new Form($this->owner,'MembershipForm',$fields,$actions);
		$this->owner->extend('updateMembershipForm', $form);
		return $form;
	}
	
	function guestcontinue(){		
		$this->owner->redirect($this->NextStepLink());
	}
	
	function LoginForm(){
		$form = new MemberLoginForm($this->owner,'LoginForm');
		$this->owner->extend('updateLoginForm', $form);
		return $form;
	}
	
	function createaccount($requestdata){
		if(Member::currentUser()){ //we shouldn't create an account if already a member
			Director::redirect($this->NextStepLink());
			return;
		}
		if(!($requestdata instanceof SS_HTTPRequest)){ //using this function to redirect, and display action
			Director::redirect($this->NextStepLink('createaccount'));
			return;
		}
		return array(
			'Form' => $this->CreateAccountForm()
		);
	}
	
	function CreateAccountForm(){
		$fields = CheckoutFieldFactory::singleton()->getMembershipFields();
		$actions = new FieldSet(
			new FormAction('docreateaccount','Create New Account')
		);
		$validator = new RequiredFields(array_keys($fields->dataFields())); //require all fields
		$form = new Form($this->owner,"CreateAccountForm",$fields,$actions, $validator);
		$this->owner->extend('updateCreateAccountForm', $form);
		return $form;
	}
	
	function docreateaccount($data, Form $form){
		$checkout = Checkout::get();
		//we want to make use the goodness of $form->saveInto, so we need to use a dummy DataObject
		$data = new DataObject();
		$form->saveInto($data);
		$member = $checkout->createMembership($data->toMap());
		//check member was created, else send back error
		if(!$member){
			$form->sessionMessage($checkout->getMessage(), $checkout->getMessageType());
			Controller::curr()->redirectBack();
			return;
		}
		$member->write();
		$member->logIn(); //log in member before continuing
		Controller::curr()->redirect($this->NextStepLink());
		return;
	}
	
}