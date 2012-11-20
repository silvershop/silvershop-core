<?php
/**
 * Factory for generating checkout fields
 * 
 * @todo 
 */
class CheckoutFieldFactory{
	
	private static $inst;
	
	static function singleton(){
		if(!self::$inst)
			self::$inst = new CheckoutFieldFactory(); 
		return self::$inst; 
	}
	
	//prevent instantiation
	private function __construct(){}
	
	function getAddressFields($type = "shipping"){
		$address = singleton('Address');
		return $address->getFormFields($type);
	}
	
	/**
	 * name + email
	 */
	function getContactFields(){
		return new FieldSet(
			new TextField('FirstName', _t('CheckoutField.FIRSTNAME','First Name')),
			new TextField('Surname', _t('CheckoutField.SURNAME','Surname')),
			new EmailField('Email', _t('CheckoutField.EMAIL','Email'))
		);
	}
	
	function getMembershipFields(){
		$fields = $this->getContactFields();
		$idfield = Member::get_unique_identifier_field();
		if(!$fields->fieldByName($idfield)){
			$fields->push(new TextField($idfield,$idfield)); //TODO: scaffold the correct id field
		}
		$fields->push(new ConfirmedPasswordField("Password"));
		return $fields;
	}
	
	function getPasswordFields(){
		$fields =  new FieldSet(
			$header = new HeaderField(_t('OrderForm.MembershipDetails','Membership Details'), 3),
			$memberinfo = new LiteralField('MemberInfo', '<p class="message warning">'._t('OrderForm.MemberInfo','If you are already a member please')." <a href=\"Security/login?BackURL=" . CheckoutPage::find_link(true) . "/\">"._t('OrderForm.LogIn','log in').'</a>.</p>'),
			$accountinfo = new LiteralField('AccountInfo', '<p>'._t('OrderForm.AccountInfo','Please choose a password, so you can login and check your order history in the future').'</p><br/>'),
			$pwf = new ConfirmedPasswordField('Password', _t('OrderForm.Password','Password'))
		);
		if(!Checkout::user_membership_required()){
			$pwf->setCanBeEmpty(true);
		}
		return $fields;
	}
	
	function getPaymentMethodFields(){
		//TODO: only get one field if there is no option
		return new OptionsetField(
			'PaymentMethod','',Payment::get_supported_methods(),array_shift(array_keys(Payment::get_supported_methods()))
		);
	}
	
	function getNotesField(){
		return new TextareaField("Notes",_t("CheckoutField.NOTES","Message"));
	}
	
	function getTermsConditionsField(){
		if(SiteConfig::current_site_config()->TermsPage()->exists()) {
			$termsPage = SiteConfig::current_site_config()->TermsPage();
			$field =  new CheckedCheckboxField('ReadTermsAndConditions', 
				sprintf(_t('CheckoutField.TERMSANDCONDITIONS',
					"I agree to the terms and conditions stated on the 
						<a href=\"%s\" target=\"new\" title=\"Read the shop terms and conditions for this site\">
							terms and conditions
						</a>
					page"),$termsPage->Link()
				)
			);
			$field->setRequiredMessage(_t("CheckoutField.MUSTAGREETOTERMS","You must agree to the terms and conditions"));
			return $field;
		}
		return null;
	}
	
}