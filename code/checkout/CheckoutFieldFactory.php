<?php
/**
 * Factory for generating checkout fields
 *
 * @todo
 */
class CheckoutFieldFactory{

	private static $inst;

	public static function singleton() {
		if(!self::$inst){
			self::$inst = new CheckoutFieldFactory();
		}
		return self::$inst;
	}

	//prevent instantiation
	private function __construct() {

	}

	public function getContactFields($subset = array()) {
		return $this->getSubset(new FieldList(
			new TextField('FirstName', _t('CheckoutField.FIRSTNAME', 'First Name')),
			new TextField('Surname', _t('CheckoutField.SURNAME', 'Surname')),
			new EmailField('Email', _t('CheckoutField.EMAIL', 'Email'))
		), $subset);
	}

	public function getAddressFields($type = "shipping", $subset = array()) {
		$address = singleton('Address');
		$fields =  $address->getFormFields($type);
		return $this->getSubset($fields, $subset);
	}

	public function getMembershipFields() {
		$fields = $this->getContactFields();
		$idfield = Member::get_unique_identifier_field();
		if(!$fields->fieldByName($idfield)){
			$fields->push(new TextField($idfield, $idfield)); //TODO: scaffold the correct id field
		}
		$fields->push($this->getPasswordField());
		return $fields;
	}

	public function getPasswordFields() {
		$loginlink = "Security/login?BackURL=".CheckoutPage::find_link(true);
		$fields =  new FieldList(
			new HeaderField(_t('CheckoutField.MEMBERSHIPDETAILS', 'Membership Details'), 3),
			new LiteralField('MemberInfo',
				'<p class="message warning">'.
					_t('CheckoutField.MEMBERINFO', 'If you are already a member please')
					." <a href=\"$loginlink\">".
						_t('OrderForm.LogIn', 'log in').
					'</a>.'.
				'</p>'
			),
			new LiteralField('AccountInfo',
				'<p>'._t('CheckoutField.ACCOUNTINFO',
					'Please choose a password, so you can login and check your order history in the future'
				).'</p>'
			),
			$this->getPasswordField()
		);
		if(!Checkout::user_membership_required()){
			$pwf->setCanBeEmpty(true);
		}
		return $fields;
	}

	public function getPaymentMethodFields() {
		//TODO: only get one field if there is no option
		return new OptionsetField(
			'PaymentMethod',
			_t("Checkout", "Payment Type"),
			GatewayInfo::get_supported_gateways(), array_keys(GatewayInfo::get_supported_gateways())
		);
	}

	public function getPasswordField($confirmed = true) {
		if($confirmed){
			return ConfirmedPasswordField::create('Password', _t('CheckoutField.PASSWORD', 'Password'));
		}
		return PasswordField::create('Password', _t('CheckoutField.PASSWORD', 'Password'));
	}

	public function getNotesField() {
		return TextareaField::create("Notes", _t("CheckoutField.NOTES", "Message"));
	}

	public function getTermsConditionsField() {
		$field = null;

		if(SiteConfig::current_site_config()->TermsPage()->exists()) {
			$termsPage = SiteConfig::current_site_config()->TermsPage();
			
			$field = CheckboxField::create('ReadTermsAndConditions',
				sprintf(_t('CheckoutField.TERMSANDCONDITIONS',
					"I agree to the terms and conditions stated on the
						<a href=\"%s\" target=\"new\" title=\"Read the shop terms and conditions for this site\">
							terms and conditions
						</a>
					page"), $termsPage->Link()
				)
			);
		}
		
		return $field;
	}

	/**
	 * Helper function for reducing a field set to a given subset,
	 * in the given order.
	 * @param  FieldList $fields form fields to take a subset from.
	 * @param  array $subset list of field names to return as subset
	 * @return FieldList subset of form fields
	 */
	private function getSubset(FieldList $fields, $subset = array()) {
		if(empty($subset)){
			return $fields;
		}
		$subfieldlist = new FieldList();
		foreach ($subset as $field) {
			if($field = $fields->fieldByName($field)){
				$subfieldlist->push($field);
			}
		}
		return $subfieldlist;
	}

}
