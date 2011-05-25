<?php
/**
  * @author Nicolaas [at] sunnysideup.co.nz
  *
  * @see CheckoutPage
  *
  * @package ecommerce
  **/

class OrderFormWithoutShippingAddress extends OrderForm {

	protected static $fixed_country_code;
		static function set_fixed_country_code($v) {self::$fixed_country_code = $v;}

	protected static $extra_fields = array();
		static function add_extra_field($tabName, $field) {self::$extra_fields[] = array("TabName" =>$tabName, "FieldObject" => $field);}

	protected static $postal_code_url = "http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder";
		static function set_postal_code_url($v) {self::$postal_code_url = $v;}

	protected static $postal_code_label = "find postcode";
		static function set_postal_code_label($v) {self::$postal_code_label = $v;}

	protected static $login_invite_alternative_text = 'Please <a href="Security/login?BackURL=/">log in now</a> to retrieve your account details or create an account below.';
		static function set_login_invite_alternative_text($v) {self::$login_invite_alternative_text = $v;}
		static function get_login_invite_alternative_text() {
			return str_replace("Security/login/", "Security/login/?BackURL=/".(CheckoutPage::find_link(true)), self::$login_invite_alternative_text) ;
		}



	function __construct($controller, $name) {

		Requirements::javascript('ecommerce/javascript/OrderFormWithoutShippingAddress.js');

		parent::__construct($controller, $name);

		//stop people adding different shipping address
		$this->unsetActionByName("action_useDifferentShippingAddress");
		$this->unsetActionByName("useDifferentShippingAddress");
		$member = Member::currentMember();
		if(!$member || !$member->ID || $member->Password == '') {
			$this->fields->addFieldToTab("", new LiteralField('MemberInfoAlso', '<p class="message good LoginCallToAction">'.self::get_login_invite_alternative_text().'</p>', $this), "FirstName");
			//improve password field TEMPORARY HACK!
			//$passwordField = new OptionalConfirmedPasswordField('Password', 'Password', '', null, true);
			//$passwordField->minLength = 6;
			//$passwordField->showOnClickTitle = "add password now";
			//$this->fields->replaceField("Password", $passwordField);
			Requirements::javascript('ecommerce/javascript/OptionalConfirmedPasswordField.js');
			Requirements::block(SAPPHIRE_DIR . '/javascript/ConfirmedPasswordField.js');
		}

		$this->fields->removeFieldFromTab("RightOrder", "MemberInfo");
		//add extra fields
		foreach(self::$extra_fields as $fieldCombo) {
			$fieldCombo["FieldObject"]->setForm($this);
			$this->fields->addFieldToTab($fieldCombo["TabName"],$fieldCombo["FieldObject"]);
		}
		//replace field for address

		foreach($this->fields->dataFields() as $i => $child) {
			if(is_object($child)){
				$name = $child->Name();
				switch ($name) {
					case "Membership Details":
						$child->setTitle('Retain Personal Details with Password');
						break;
					case "Address":
						$child->setTitle('Street Address');
						break;
					case "AddressLine2":
						$child->setRightTitle('<a href="'.self::$postal_code_url.'" id="OrderFormWithoutShippingAddressPostalCodeLink">'.self::$postal_code_label.'</a>');
						$child->setTitle('Postal Code');
						break;
					case "Country":
						if(self::$fixed_country_code) {
							$child->setValue(self::$fixed_country_code);
							ShoppingCart::set_country(self::$fixed_country_code);
						}
						break;
					default:
						break;
				}
			}
		}



		$this->fields->addFieldToTab("", new TextareaField('CustomerOrderNote', 'Note / Question', 7, 30, null, $this));

		$data = $this->getData();
		$this->loadDataFrom($data);
	}

	function processOrder($data, $form, $request) {
		return parent::processOrder($data, $form, $request);
	}

}

