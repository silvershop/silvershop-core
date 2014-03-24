<?php

/**
 * Provides:
 * 	- member identifier, and password fields.
 * 	- required membership fields
 * 	- validating data
 * 
 */
class MembershipCheckoutComponent extends CheckoutComponent{

	protected $confirmed;
	protected $passwordvalidator;

	protected $dependson = array(
		'CustomerDetailsCheckoutComponent'
	);

	public function __construct($confirmed = true, $validator = null) {
		$this->confirmed = $confirmed;
		if(!$validator){
			$this->passwordvalidator = Member::password_validator();
			if(!$this->passwordvalidator){
				$this->passwordvalidator = new PasswordValidator();
				$this->passwordvalidator->minLength(5);
				$this->passwordvalidator->characterStrength(2, array("lowercase", "uppercase", "digits", "punctuation"));
			}
		}
	}

	public function getFormFields(Order $order, Form $form = null) {
		$fields = new FieldList();
		if(Member::currentUserID()){
			return $fields;
		}
		$idfield = Member::config()->unique_identifier_field;
		if(!$order->{$idfield} &&
			($form && !$form->Fields()->fieldByName($idfield))){
				//TODO: scaffold the correct id field type
				$fields->push(new TextField($idfield, $idfield));
		}
		$fields->push($this->getPasswordField());
		return $fields;
	}

	public function getRequiredFields(Order $order) {
		if(Member::currentUserID() || !Checkout::membership_required()){
			return array();
		}
		return array(
			Member::get_unique_identifier_field(),
			'Password'
		);
	}

	public function getPasswordField() {
		if($this->confirmed){
			//relies on fix: https://github.com/silverstripe/silverstripe-framework/pull/2757
			return ConfirmedPasswordField::create('Password', _t('CheckoutField.PASSWORD', 'Password'))
					->setCanBeEmpty(!Checkout::membership_required());
		}
		return PasswordField::create('Password', _t('CheckoutField.PASSWORD', 'Password'));
	}

	public function validateData(Order $order, array $data) {
		if(Member::currentUserID()){
			return;
		}
		$result = new ValidationResult();
		if(Checkout::membership_required() || !empty($data['Password'])){
			$member = new Member($data);
			$idfield = Member::config()->unique_identifier_field;
			$idval = $data[$idfield];
			if(ShopMember::get_by_identifier($idval)){
				$result->error(
					sprintf(
						_t("Checkout.MEMBEREXISTS", "A member already exists with the %s %s"),
						$idfield, $idval
					), $idval
				);
			}
			$passwordresult = $this->passwordvalidator->validate($data['Password'], $member);
			if(!$passwordresult->valid()){
				$result->error($passwordresult->message(), "Password");
			}
		}
		if(!$result->valid()){
			throw new ValidationException($result);
		}
	}

	public function getData(Order $order) {
		$data = array();

		if($member = Member::currentUser()){
			$idf = Member::config()->unique_identifier_field;
			$data[$idf] = $member->{$idf};
		}
		return $data;
	}

	/**
	 * @throws ValidationException
	 */
	public function setData(Order $order, array $data) {
		if(Member::currentUserID()){
			return;
		}
		if(!Checkout::membership_required() && empty($data['Password'])){
			return;
		}

		$factory = new ShopMemberFactory();
		$member = $factory->create($data);
		$member->write();
		$member->logIn();
	}

	public function setConfirmed($confirmed) {
		$this->confirmed = $confirmed;

		return $this;
	}

}
