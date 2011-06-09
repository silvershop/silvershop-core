<?php
 /**
	* Order form that allows a user to purchase their
	* order items on the
	*
	* @see CheckoutPage
	*
	* @package ecommerce
	*/
class OrderForm extends Form {
	
	//TODO: name these better so they can be understood
	
	//optional for user to become a member
	protected static $user_membership_optional = false;
		static function set_user_membership_optional($optional = true){self::$user_membership_optional = $optional;}
	
	//all users must become members if true, or won't become members if false
	protected static $force_membership = true;
		static function set_force_membership($force = false){self::$force_membership = $force;}

	//actions that don't need validation
	protected $validactions = array(
		'useDifferentShippingAddress',
		'useMemberShippingAddress'
	);

	function __construct($controller, $name) {
		//Requirements::themedCSS('OrderForm');

		// 1) Member and shipping fields
		$member = Member::currentUser();
		$memberFields = new CompositeField(singleton('Member')->getEcommerceFields());
		$requiredFields = singleton('Member')->getEcommerceRequiredFields();

		if(ShoppingCart::uses_different_shipping_address()) {
			$countryField = new DropdownField('ShippingCountry',  _t('OrderForm.Country','Country'), Geoip::getCountryDropDown(), EcommerceRole::find_country());
			$shippingFields = new CompositeField(
				new HeaderField(_t('OrderForm.SendGoodsToDifferentAddress','Send goods to different address'), 3),
				new LiteralField('ShippingNote', '<p class="message warning">'._t('OrderForm.ShippingNote','Your goods will be sent to the address below.').'</p>'),
				new LiteralField('Help', '<p>'._t('OrderForm.Help','You can use this for gift giving. No billing information will be disclosed to this address.').'</p>'),
				new TextField('ShippingName', _t('OrderForm.Name','Name')),
				new TextField('ShippingAddress', _t('OrderForm.Address','Address')),
				new TextField('ShippingAddress2', _t('OrderForm.Address2','')),
				new TextField('ShippingCity', _t('OrderForm.City','City')),
				$countryField,
				new HiddenField('UseShippingAddress', '', true),
				$changeshippingbutton = new FormAction_WithoutLabel('useMemberShippingAddress', _t('OrderForm.UseBillingAddress','Use Billing Address for Shipping'))
			);
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			$changeshippingbutton->setButtonContent(_t('OrderForm.UseBillingAddress','Use Billing Address for Shipping'));
			$changeshippingbutton->useButtonTag = true;

			$requiredFields[] = 'ShippingName';
			$requiredFields[] = 'ShippingAddress';
			$requiredFields[] = 'ShippingCity';
			$requiredFields[] = 'ShippingCountry';
		} else {
			$countryField = $memberFields->fieldByName('Country');
			$shippingFields = new FormAction_WithoutLabel('useDifferentShippingAddress', _t('OrderForm.useDifferentShippingAddress', 'Use Different Shipping Address'));
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			$shippingFields->setButtonContent(_t('OrderForm.useDifferentShippingAddress', 'Use Different Shipping Address'));
			$shippingFields->useButtonTag = true;
		}
		
		if($countryField){
			$countryField->addExtraClass('ajaxCountryField');

			$setCountryLinkID = $countryField->id() . '_SetCountryLink';
			$setContryLink = ShoppingCart::set_country_link();
			$memberFields->push(new HiddenField($setCountryLinkID, '', $setContryLink));
		}
		
		$leftFields = new CompositeField($memberFields, $shippingFields);
		$leftFields->setID('LeftOrder');

		$rightFields = new CompositeField();
		$rightFields->setID('RightOrder');
		
		
		if(!$member) {
			$rightFields->push(new HeaderField(_t('OrderForm.MembershipDetails','Membership Details'), 3));
			$rightFields->push(new LiteralField('MemberInfo', '<p class="message warning">'._t('OrderForm.MemberInfo','If you are already a member please')." <a href=\"Security/login?BackURL=" . CheckoutPage::find_link(true) . "/\">"._t('OrderForm.LogIn','log in').'</a>.</p>'));
			$rightFields->push(new LiteralField('AccountInfo', '<p>'._t('OrderForm.AccountInfo','Please choose a password, so you can login and check your order history in the future').'</p><br/>'));
			$rightFields->push(new FieldGroup($pwf = new ConfirmedPasswordField('Password', _t('OrderForm.Password','Password'))));
			
			//if user doesn't fill out password, we assume they don't want to become a member
			//TODO: allow different ways of specifying that you want to become a member
			if(self::$user_membership_optional){ $pwf->setCanBeEmpty(true);	}
			
			if(self::$force_membership || !self::$user_membership_optional){
				$requiredFields[] = 'Password[_Password]';
				$requiredFields[] = 'Password[_ConfirmPassword]';
				//TODO: allow extending this to provide other ways of indicating that you want to become a member
			}
			
		}else{
			$rightFields->push(new LiteralField('MemberInfo', '<p class="message good">'.sprintf(_t('OrderForm.LoggedInAs','You are logged in as %s.'),$member->getName())." <a href=\"Security/logout?BackURL=" . CheckoutPage::find_link(true) . "/\">"._t('OrderForm.LogOut','log out').'</a>.</p>'));
		}

		// 2) Payment fields
		$currentOrder = ShoppingCart::current_order();
		$totalobj = DBField::create('Currency',$currentOrder->Total()); //should instead be $totalobj = $currentOrder->dbObject('Total');

		$paymentFields = Payment::combined_form_fields($totalobj->Nice());
		foreach($paymentFields as $field) $rightFields->push($field);

		if($paymentRequiredFields = Payment::combined_form_requirements()) $requiredFields = array_merge($requiredFields, $paymentRequiredFields);

		// 3) Put all the fields in one FieldSet
		$fields = new FieldSet($leftFields, $rightFields);

		// 4) Terms and conditions field
		// If a terms and conditions page exists, we need to create a field to confirm the user has read it
		if($controller->TermsPageID && $termsPage = DataObject::get_by_id('Page', $controller->TermsPageID)) {
			$bottomFields = new CompositeField(new CheckboxField('ReadTermsAndConditions', "I agree to the terms and conditions stated on the <a href=\"$termsPage->URLSegment\" title=\"Read the shop terms and conditions for this site\">terms and conditions</a> page"));
			$bottomFields->setID('BottomOrder');
			$fields->push($bottomFields);
			$requiredFields[] = 'ReadTermsAndConditions';
		}

		// 5) Actions and required fields creation
		$actions = new FieldSet(new FormAction('processOrder', _t('OrderForm.processOrder','Place order and make payment')));
		$requiredFields = new CustomRequiredFields($requiredFields);
		$this->extend('updateValidator',$requiredFields);

		$this->extend('updateFields',$fields);

		// 6) Form construction
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);

		// 7) Member details loading
		if($member && $member->ID) $this->loadDataFrom($member);

		// 8) Country field value update
		if($countryField){
			$currentOrder = ShoppingCart::current_order();
			$currentOrderCountry = $currentOrder->findShippingCountry(true);
			$countryField->setValue($currentOrderCountry);
		}

		//allow updating via decoration
		$this->extend('updateForm',$this);
	}


	function addValidAction($action){
		$this->validactions[] = $action;
	}

	function getValidActions($format = true){
		$vas = $this->validactions;
		if($format){
			$actions = array();
			foreach($vas as $action){
				$actions[] = 'action_'.$action;
			}
		}
		return $actions;
	}
	
	
	/**
	 * Validation of various pre-processing things.
	 * @return valid or not.
	 */
	function validate(){
		//always validate on order processing
	 	if(isset($_POST['action_processOrder'])){
	 		//TODO: check items are in cart
			//TODO: Check if prices have changed
	 		
	 		$valid = parent::validate();
	 			 		
	 		//TODO: check that member details are not already taken, if entered
			//Chekc payment method is valid
	 		return $valid;
	 	} 
	 	
	 	//Override form validation to make different shipping address button, and other form actions work
		foreach($this->getValidActions() as $action){
			if(isset($_POST[$action])){return true;}
		}
		return parent::validate();
	 }

	/**
	 * Save in the session that the current member wants to use a different shipping address.
	 */
	function useDifferentShippingAddress($data, $form, $request) {
		ShoppingCart::set_uses_different_shipping_address(true);
		$this->saveDataToSession($data);
		Director::redirectBack();
	}

	/**
	 * Save in the session that the current member wants to use his address as a shipping address.
	 */
	function useMemberShippingAddress($data, $form, $request) {
		ShoppingCart::set_uses_different_shipping_address(false);
		$this->saveDataToSession($data);
		Director::redirectBack();
	}

	function updateShippingCountry($data, $form, $request) {
		Session::set($this->FormName(), $data);
		ShoppingCart::set_country($data['Country']);
		if(Director::is_ajax()){
			return "success";
		}
		Director::redirectBack();
	}

	/**
	 * Process the items in the shopping cart from session,
	 * creating a new {@link Order} record, and updating the
	 * customer's details {@link Member} record.
	 *
	 * {@link Payment} instance is created, linked to the order,
	 * and payment is processed {@link Payment::processPayment()}
	 *
	 * @param array $data Form request data submitted from OrderForm
	 * @param Form $form Form object for this action
	 * @param HTTPRequest $request Request object for this action
	 */
	function processOrder($data, $form, $request) {		
		
		$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
		$payment = class_exists($paymentClass) ? new $paymentClass() : null;

		if(!($payment && $payment instanceof Payment)) {
			user_error(get_class($payment) . ' is not a valid Payment object!', E_USER_ERROR); //TODO: be more graceful with errors
		}
		$this->saveDataToSession($data); //save for later if necessary
		
		//check for cart items
		if(!ShoppingCart::has_items()) {
			$form->sessionMessage(_t('OrderForm.NoItemsInCart','Please add some items to your cart'), 'bad');
			Director::redirectBack();
			return false;
		}

		//check that price hasn't changed
		$oldtotal = ShoppingCart::current_order()->Total();

		// Create new Order from shopping cart, discard cart contents in session
		$order = ShoppingCart::current_order();
		if($order->Total() != $oldtotal) {
			$form->sessionMessage(_t('OrderForm.PriceUpdated','The order price has been updated'), 'warning');
			Director::redirectBack();
			return false;
		}

		$member = Member::currentUser();
		if(!$member){
			if(self::$user_membership_optional){
				if($this->userWantsToBecomeMember($data,$form)){
					$member = EcommerceRole::ecommerce_create_or_merge($data);
				}
				//otherwise we assume they don't want to become a member
			}elseif(self::$force_membership){
				//create member
				$member = EcommerceRole::ecommerce_create_or_merge($data);
			}
		}
		
		//if they are a member, or if they have filled out the member fields (password, save my details)
		// Create new OR update logged in {@link Member} record
		if($member === false) {
			$form->sessionMessage(
				_t('OrderForm.MEMBEREXISTS', 'Sorry, a member already exists with that email address.
					If this is your email address, please log in first before placing your order.'
				),
				'bad'
			);
			Director::redirectBack();
			return false;
		}
		
		//assiciate member with order, if there is a member now
		if($member){
			$member->write();
			$member->logIn();
			if($member)	$payment->PaidByID = $member->ID;
			$order->MemberID = $member->ID;
		}
		
		// Write new record {@link Order} to database
		$form->saveInto($order);
		$order->save(); //sets status to 'Unpaid' //is it even necessary to have it's own function? ..just legacy code.

		$this->clearSessionData(); //clears the stored session form data that might have been needed if validation failed
		
		// Save payment data from form and process payment
		$form->saveInto($payment);
		$payment->OrderID = $order->ID;
		$payment->PaidForID = $order->ID;
		$payment->PaidForClass = $order->class;
		
		$payment->Amount->Amount = $order->Total();
		$payment->write();
		
		//prepare $data - ie put into the $data array any fields that may need to be there for payment

		// Process payment, get the result back
		$result = $payment->processPayment($data, $form);
		
		// isProcessing(): Long payment process redirected to another website (PayPal, Worldpay)
		if($result->isProcessing()) {
			return $result->getValue();
		}

		if($result->isSuccess()) {
			$order->sendReceipt();
		}

		Director::redirect($order->Link());
		return true;
	}
	
	/**
	 * Detect if user wants to become a member at checkout.
	 */
	protected function userWantsToBecomeMember($data,$form){
		$want = isset($data['Password']) && is_array($data['Password']) && isset($data['Password']['_Password'])	&& $data['Password']['_Password'] != "";
		$this->extend('wantsToBecomeMember',$want);
		return $want;
	}

	function saveDataToSession($data){
		Session::set("FormInfo.{$this->FormName()}.data", $data);
	}

	function loadDataFromSession(){
		if($data = Session::get("FormInfo.{$this->FormName()}.data")){
			$this->loadDataFrom($data);
		}
	}
	
	function clearSessionData(){
		Session::set("FormInfo.{$this->FormName()}.data", null);		
	}

}