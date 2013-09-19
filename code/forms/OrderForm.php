<?php
/**
* Shows on the checkout page, when completing an order.
*
* @package shop
* @subpackage forms
*/
class OrderForm extends Form {

	//actions that don't need validation
	protected $validactions = array(
		'useDifferentShippingAddress',
		'useMemberShippingAddress'
	);

	function __construct($controller, $name) {
		$cff = CheckoutFieldFactory::singleton();
		
		//Member and shipping fields
		$member = Member::currentUser();
		$addressSingleton = singleton('Address');
		$order = ShoppingCart::curr();
				
		$basefields = $cff->getContactFields();
		$basefields->push(
			new HeaderField("ShippingHeading",_t('OrderForm.ShippingAndBillingAddress','Shipping and Billing Address'), 3)
		);
		$basefields->merge($cff->getAddressFields("Shipping"));
		$orderFields = new CompositeField($basefields);		
		
		$requiredFields = $addressSingleton->getRequiredFields('Shipping');
			
		if($order && $order->SeparateBillingAddress) {
			$orderFields->fieldByName("ShippingHeading")->setTitle(_t('OrderForm.ShippingAddress','Shipping Address'));
			$billingFields = new CompositeField(
				new HeaderField(_t('OrderForm.BillingAddress','Billing Address'), 3),
				new HiddenField('SeparateBillingAddress', '', true)
			);
			$billingFields->FieldList()->merge($addressSingleton->getFormFields('Billing'));
			$billingFields->push($changeshippingbutton = new FormAction('useMemberShippingAddress', _t('OrderForm.ShippingIsBilling','Use Shipping Address for Billing')));
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			$changeshippingbutton->setButtonContent(_t('OrderForm.ShippingIsBilling','Use Shipping Address for Billing'));
			$changeshippingbutton->useButtonTag = true;
			$requiredFields = array_merge($requiredFields,$addressSingleton->getRequiredFields('Billing'));
		} else {		
			$billingFields = new FormAction('useDifferentShippingAddress', _t('OrderForm.DifferentBillingAddress', 'Use Different Billing Address'));
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			$billingFields->setButtonContent(_t('OrderForm.DifferentBillingAddress', 'Use Different Billing Address'));
			$billingFields->useButtonTag = true;
		}

		$leftFields = new CompositeField($orderFields, $billingFields);
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
			if(self::$user_membership_optional){
				$pwf->setCanBeEmpty(true);
			}
			if(self::$force_membership || !self::$user_membership_optional){
				$requiredFields[] = 'Password[_Password]';
				$requiredFields[] = 'Password[_ConfirmPassword]';
				//TODO: allow extending this to provide other ways of indicating that you want to become a member
			}
		}else{
			$rightFields->push(new LiteralField('MemberInfo', '<p class="message good">'.sprintf(_t('OrderForm.LoggedInAs','You are logged in as %s.'),$member->getName())." <a href=\"Security/logout?BackURL=" . CheckoutPage::find_link(true) . "/\">"._t('OrderForm.LogOut','log out').'</a>.</p>'));
		}
		//Payment fields
		$rightFields->push(new HeaderField(_t('Payment.PAYMENTTYPE', 'Payment Type'), 3));
		$rightFields->push($cff->getPaymentMethodFields());
		$rightFields->push(new ReadonlyField('Amount', _t('Payment.AMOUNT', 'Amount'), DBField::create_field('Currency',$order->Total())->Nice()));

		//Put all the fields in one FieldList
		$fields = new FieldList($leftFields, $rightFields);
		$bottomFields = new CompositeField(
			new TextareaField("Notes","Message")
		);
		$bottomFields->setID('BottomOrder');
		
		//Terms and conditions field
		if($termsfield = $cff->getTermsConditionsField()){
			$bottomFields->push($termsfield);
		}
		
		$fields->push($bottomFields);
		
		//Actions and required fields creation
		$actions = new FieldList(new FormAction('processOrder', _t('OrderForm.processOrder','Place order and make payment')));
		$requiredFields = new RequiredFields();
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);	
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
	 		$data = $this->getData();
	 		$this->saveDataToSession($data);
	 		//check items are in cart, and each item can be purchased
	 		$order = ShoppingCart::singleton()->current();
	 		if(!$order){
	 			return false;
	 		}
	 		$items = $order->Items();
	 		if($items->exists()){
		 		foreach($items as $item){
		 			if(!$item->Buyable()->canPurchase()){
		 				$this->sessionMessage(sprintf(_t("OrderForm.PRODUCTCANTPURCHASE","%s cannot be purchased"),$item->Title), "bad");
		 				return false;
		 			}		 				
		 		}
	 		}else{
	 			$this->sessionMessage(_t("OrderForm.EMPTYCART","Your cart is empty"), $type);
	 			return false;
	 		}
			//TODO: Check if prices have changed
	 		$valid = parent::validate();
	 		//TODO: check that member details are not already taken, if entered
	 		//check terms have been accepted
			$controller = $this->Controller();
	 		if(SiteConfig::current_site_config()->TermsPage()->exists() && (!isset($data['ReadTermsAndConditions']) || !(bool)$data['ReadTermsAndConditions'])){
	 			$this->sessionMessage(_t("OrderForm.MUSTREADTERMS","You must agree to terms and conditions"), "bad");
	 			return false;
	 		}
	 		return $valid;
	 	}
	 	//Override form validation to make different shipping address button, and other form actions work
		foreach($this->getValidActions() as $action){
			if(isset($_POST[$action])){
				return true;
			}
		}
		return parent::validate();
	 }

	/**
	 * Save in the session that the current member wants to use a different shipping address.
	 */
	function useDifferentShippingAddress($data, $form, $request) {
		$order = ShoppingCart::curr();
		$order->SeparateBillingAddress = true;
		$order->write();
		$this->saveDataToSession($data);
		Controller::curr()->redirectBack();
	}

	/**
	 * Save in the session that the current member wants to use his address as a shipping address.
	 */
	function useMemberShippingAddress($data, $form, $request) {
		$order = ShoppingCart::curr();
		$order->SeparateBillingAddress = false;
		$order->write();
		$this->saveDataToSession($data);
		Controller::curr()->redirectBack();
	}

	function updateShippingCountry($data, $form, $request) {
		Session::set($this->FormName(), $data);
		$order = ShoppingCart::curr();
		$order->Country = $data['Country'];
		$order->write();		
		if(Director::is_ajax()){
			return "success";
		}
		Controller::curr()->redirectBack();
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
	function processOrder($data, $form) {
		$checkout = Checkout::get();
		$this->saveDataToSession($data); //save for later if necessary ...shouldn't technically be needed, if order is being written?
		$cart = ShoppingCart::singleton();
		$order = $cart->current();
		$form->saveInto($order);
		
		$member = Member::currentUser();
		if(!$member){
			$member = new DataObject(); //dummy dataobject to handle the goodness of $form->saveInto
			$form->saveInto($member);
			$member = $checkout->createMembership($member->toMap());
			if(!$member){
				$form->sessionMessage($checkout->getMessage(),$checkout->getMessageType());
				Controller::curr()->redirectBack();
				return;
			}
		}
		//save addresses
		$address = $this->saveAddress($order->getShippingAddress(),$form,$member);
		$order->ShippingAddressID = $address->ID;
		if(!$order->SeparateBillingAddress){
			$order->BillingAddressID = $order->ShippingAddressID;
		}else{
			$address = $this->saveAddress($order->getBillingAddress(),$form,$member,true);
			$order->BillingAddressID = $address->ID;
		}
		$order->write();
		
		//TODO: check that price hasn't changed
		$processor = OrderProcessor::create($order);
		if(!$processor->placeOrder($member)){
			$form->sessionMessage($processor->getError(), 'bad');
			Controller::curr()->redirectBack();
			return false;
		}
		$cart->clear();
		$this->clearSessionData(); //clears the stored session form data that might have been needed if validation failed
		// Save payment data from form and process payment
		$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
		$payment = $processor->createPayment($paymentClass);
		if(!$payment){
			$form->sessionMessage($processor->getError(), 'bad');
			Controller::curr()->redirect($order->Link());
			return false;
		}
		
		$payment->ReturnURL = $order->Link(); //set payment return url
		
		//prepare $data - ie put into the $data array any fields that may need to be there for payment
		$data['Reference'] = $order->Reference;
		
		// Process payment, get the result back
		$result = $payment->processPayment($data, $form);
		if($result->isProcessing()) { // isProcessing(): Long payment process redirected to another website (PayPal, Worldpay)
			return $result->getValue();
		}
		if($result->isSuccess()) {
			$processor->sendReceipt();
		}
		Controller::curr()->redirect($payment->ReturnURL);
		return true;
	}
	
	private function saveAddress($address = null,$form,$member,$billing = false){
		if(!$address){
			$address = new Address();
		}
		$prefix = ($billing) ? "Billing" : "Shipping";
		$fieldmap = $address->getFieldMap($prefix);
		
		$form->saveInto($address,$fieldmap); //TODO: provide mapping of BillingFields => AddressFields
		if($member){
			$address->MemberID = $member->ID;
		}
		if(!$address->isInDB()){
			$address->write();
		}elseif($address->isChanged()){
			$address = $address->duplicate(); //save a copy
		}
		return $address;
	}
	
	/**
	 * Override saveInto to allow custom form field to model field mapping.
	 */
	function saveInto(DataObjectInterface $dataObject, $fieldList = null){
		$this->mapFieldNames($fieldList);
		parent::saveInto($dataObject,$fieldList);
		$this->restoreFieldNames($fieldList);
	}
	
	/**
	 * Override loadDataFrom to allow custom form field to model field mapping.
	 */
	function loadDataFrom($data,$clearMissingFields = false, $fieldList = null){
		$this->mapFieldNames($fieldList);
		parent::loadDataFrom($data,$clearMissingFields,$fieldList);
		$this->restoreFieldNames($fieldList);
	}
	
	protected function mapFieldNames($fieldList){
		if(!is_array($fieldList) || empty($fieldList))
			return false;
		//rename other fields temporarly, incase they get overwritten
		$savableFields = $this->fields->saveableFields();
		foreach($savableFields as $field){
			$field->originalFieldName = $field->getName();
			$field->setName($field->originalFieldName."_renamed");
		}
		foreach($fieldList as $formfield => $modelfield){
			if(!is_int($formfield) && isset($savableFields[$formfield]) && $field = $savableFields[$formfield]){
				$field->originalFieldName = $formfield;
				$field->setName($modelfield);
			}
		}
	}
	
	protected function restoreFieldNames($fieldList){
		if(!is_array($fieldList) || empty($fieldList))
			return false;
		foreach($this->fields->saveableFields() as $field){
			if($field->originalFieldName){
				$field->setName($field->originalFieldName);
				$field->originalFieldName = null;
			}
		}
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
	
	function forTemplate(){
		$script =<<<JS
        Behaviour.register({
            '#OrderForm_OrderForm': {
                onsubmit: function(e){
                    var action = e.explicitOriginalTarget.attributes[0].value;
                    if(action == "action_useDifferentShippingAddress" || action == "action_useMemberShippingAddress"){
                        return;
                    }
                    return this.validate();
                }
            }
        });
JS;
		$form =  parent::forTemplate();
		if($this->validator)
			Requirements::customScript($script);
		return $form;
	}

}