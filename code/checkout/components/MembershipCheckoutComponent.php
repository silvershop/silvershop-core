<?php

class MembershipCheckoutComponent extends CheckoutComponent{

	protected $confirmed, $passwordvalidator;

	function __construct($confirmed = true, $validator = null){
		$this->confirmed = $confirmed;
		if(!$validator){
			$this->passwordvalidator = Member::password_validator();
			if(!$this->passwordvalidator){
				$this->passwordvalidator = new PasswordValidator();
				$this->passwordvalidator->characterStrength(3,
					array("lowercase", "uppercase", "digits", "punctuation")
				);
			}
		}
	}
	
	public function getFormFields(Order $order, Form $form = null){
		$fields = new FieldList();
		if(Member::currentUserID()){
			return $fields;
		}
		$idfield = Member::get_unique_identifier_field();
		if(!$order->{$idfield} &&
			($form && !$form->Fields()->fieldByName($idfield))){
				$fields->push(new TextField($idfield,$idfield)); //TODO: scaffold the correct id field
		}
		$fields->push($this->getPasswordField());
		return $fields;
	}

	public function getRequiredFields(Order $order) {
		if(Member::currentUserID() || !Checkout::membership_required()){
			return array();
		}
		return array(
			'Password'
		);
	}


	public function getPasswordField(){
		if($this->confirmed){
			//relies on fix: https://github.com/silverstripe/silverstripe-framework/pull/2757
			return ConfirmedPasswordField::create('Password', _t('CheckoutField.PASSWORD','Password'))
					->setCanBeEmpty(!Checkout::membership_required());
		}
		return PasswordField::create('Password', _t('CheckoutField.PASSWORD','Password'));
	}

	public function validateData(Order $order, array $data){

		if(Member::currentUserID()){
			return;
		}

		$result = new ValidationResult();
		$member = new Member($data);

		//TODO: check for an existing member using the same details
		//Member::get_unique_identifier_field()
		$idfield = Member::get_unique_identifier_field();
		
		// $idval = $data[$idfield];
		// if(ShopMember::get_by_identifier($idval)){
		// 	$result->error(sprintf(_t("Checkout.MEMBEREXISTS","A member already exists with the %s %s"),$idfield,$idval), $idval);
		// }

		if(Checkout::membership_required() || !empty($data['Password'])){

			$passwordresult = $this->passwordvalidator->validate($data['Password'], $member);
			if(!$passwordresult->valid()){
				$result->error($passwordresult->message(), "Password");
			}
		}
		
		if(!$result->valid()){
			throw new ValidationException($result);
		}
	}

	public function getData(Order $order){
		$data = array();

		if($member = Member::currentUser()){
			$idf = Member::get_unique_identifier_field();
			$data[$idf] = $member->{$idf};
		}
		return $data;
	}

	public function setData(Order $order, array $data){
		//create member?? (don't really want to do this until order is placed)
		
		// actually, yes create a member!

		$member = new Member($data);
		$validation = $member->validate();
		if(!$validation->valid()){
			return $this->error($validation->message());	//TODO need to handle i18n here?
		}

	}

	function setConfirmed($confirmed){
		$this->confirmed = $confirmed;

		return $this;
	}

}