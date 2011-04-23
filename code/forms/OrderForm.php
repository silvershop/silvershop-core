<?php

/**
 * @Description: form to submit order.
 * @see CheckoutPage
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class OrderForm extends Form {

	function __construct($controller, $name) {
		$order = ShoppingCart::current_order();
		//Requirements::themedCSS('OrderForm');
		Requirements::javascript('ecommerce/javascript/OrderForm.js');

		// 1) Member fields
		$member = Member::currentUser();
		if(!$member) {
			$member = new Member();
		}

		$memberFields = new CompositeField($member->getEcommerceFields());
		$requiredFields = $member->getEcommerceRequiredFields();

		$leftFields = new CompositeField($memberFields);
		$leftFields->setID('LeftOrder');

		$rightFields = new CompositeField();
		$rightFields->setID('RightOrder');


		if(!$member || !$member->ID || $member->Password == '') {
			$passwordField = new ConfirmedPasswordField('Password', _t('Order.PASSWORD','Password'));
			//allow people to purchase without creating a password
			$passwordField->setCanBeEmpty(false);
			//login invite right on the top
			$rightFields->push(new HeaderField(_t('Order.MEMBERSHIPDETAILS','Account Details'), 3));
			if(!$member->Created) {
				$rightFields->push(new LiteralField('MemberInfo', '<p class="message good">'._t('Order.MEMBERINFO','If you are already a member please')." <a href=\"Security/login?BackURL=" . CheckoutPage::find_link(true) . "/\">"._t('Order.LOGIN','log in').'</a>.</p>'));
			}
			$rightFields->push(new LiteralField('AccountInfo', '<p>'._t('Order.ACCOUNTINFO',
				'Please <a href="#Password" class="choosePassword">choose a password</a>, so you can log in and check your order history in the future.').'</p><br/>'));
			$rightFields->push(new FieldGroup($passwordField));
			$requiredFields[] = 'Password[_Password]';
			$requiredFields[] = 'Password[_ConfirmPassword]';
			Requirements::customScript('jQuery("#ChoosePassword").click();');
		}

		// 2) Payment fields
		$bottomFields = new CompositeField();
		$totalAsCurrencyObject = $order->TotalAsCurrencyObject(); //should instead be $totalobj = $order->dbObject('Total');
		$paymentFields = Payment::combined_form_fields($totalAsCurrencyObject->Nice());
		foreach($paymentFields as $paymentField) {
			if($paymentField->class == "HeaderField") {
				$paymentField->setTitle(_t("Order.MAKEPAYMENT", "Make Payment"));
			}
			$bottomFields->push($paymentField);
		}
		if($paymentRequiredFields = Payment::combined_form_requirements()) {
			$requiredFields = array_merge($requiredFields, $paymentRequiredFields);
		}
		$bottomFields->setID('BottomOrder');
		$finalFields = new CompositeField();
		$finalFields->setID('FinalFields');
		$finalFields->push(new HeaderField(_t('Order.COMPLETEORDER','Complete Order'), 3));
		// 3) Terms and conditions field
		// If a terms and conditions page exists, we need to create a field to confirm the user has read it
		if($controller->TermsPageID && $termsPage = DataObject::get_by_id('Page', $controller->TermsPageID)) {
			$finalFields->push(new CheckboxField('ReadTermsAndConditions', _t('Order.AGREEWITHTERMS1','I agree to the terms and conditions stated on the ').' <a href="'.$termsPage->URLSegment.'">'.Convert::raw2xml($termsPage->Title).'</a> '._t('Order.AGREEWITHTERMS2','page.')));
			$requiredFields[] = 'ReadTermsAndConditions';
		}

		$finalFields->push(new TextareaField('CustomerOrderNote', _t('Order.CUSTOMERNOTE','Note / Question'), 7, 30));

		// 4) Put all the fields in one FieldSet
		$fields = new FieldSet($rightFields, $leftFields, $bottomFields, $finalFields);

		// 5) Actions and required fields creation
		$actions = new FieldSet(new FormAction('processOrder', _t('Order.PROCESSORDER','Place order and make payment')));
		$requiredFields = new OrderForm_Validator($requiredFields);
		$this->extend('updateValidator',$requiredFields);
		$this->extend('updateFields',$fields);
		Requirements::javascript('ecommerce/javascript/OrderFormWithShippingAddress.js');
		if(Order::get_add_shipping_fields()) {
			$countriesForDropdown = EcommerceRole::list_of_allowed_countries_for_dropdown();
			$countryField = new DropdownField('ShippingCountry',  _t('Order.COUNTRY','Country'), $countriesForDropdown, ShoppingCart::get_country());
			if(count($countriesForDropdown) == 1) {
				$countryField = $countryField->performReadonlyTransformation();
			}
			$shippingFields = new CompositeField(
				new HeaderField(_t('Order.SENDGOODSTODIFFERENTADDRESS','Send goods to different address'), 3),
				new LiteralField('ShippingNote', '<p class="message warning">'._t('Order.SHIPPINGNOTE','Your goods will be sent to the address below.').'</p>'),
				new LiteralField('Help', '<p>'._t('Order.SHIPPINGHELP','You can use this for gift giving. No billing information will be disclosed to this address.').'</p>'),
				new TextField('ShippingName', _t('Order.NAME','Name')),
				new TextField('ShippingAddress', _t('Order.ADDRESS','Address')),
				new TextField('ShippingAddress2', _t('Order.ADDRESS2','')),
				new TextField('ShippingCity', _t('Order.CITY','City')),
				new TextField('ShippingPostalCode', _t('Order.SHIPPINGPOSTALCODE','Postal Code')),
				$countryField
			);
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			//$requiredFields[] = 'ShippingName';
			//$requiredFields[] = 'ShippingAddress';
			//$requiredFields[] = 'ShippingCity';
			//$requiredFields[] = 'ShippingCountry';
			$shippingFields->SetID('ShippingFields');
			$shippingFields->setForm($this);
			$fields->insertBefore(new CheckboxField('UseShippingAddress', _t('Order.USEDIFFERENTSHIPPINGADDRESS', 'Use Alternative Delivery Address')), 'BottomOrder');
			$fields->insertBefore($shippingFields, 'BottomOrder');
		}
		// 6) Form construction
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);

		// 7)  Load saved data
		if($order) {
			$this->loadDataFrom($order);
			if(Order::get_add_shipping_fields()) {
				if ($shippingAddress = $order->ShippingAddress()) {
					$this->loadDataFrom($shippingAddress);
				}
			}
		}

		if($member->ID) {
			$member->Country = ShoppingCart::get_country();
			$this->loadDataFrom($member);
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
		$this->saveDataToSession($data); //save for later if necessary
		//check for cart items
		if(!ShoppingCart::has_items()) {
			// WE DO NOT NEED THE THING BELOW BECAUSE IT IS ALREADY IN THE TEMPLATE AND IT CAN LEAD TO SHOWING ORDER WITH ITEMS AND MESSAGE
			//$form->sessionMessage(_t('Order.NOITEMSINCART','Please add some items to your cart.'), 'bad');
			Director::redirectBack();
			return false;
		}

		//RUN UPDATES TO CHECK NOTHING HAS CHANGED
		$order = ShoppingCart::current_order();
		$oldtotal = $order->Total();
		// Create new Order from shopping cart, discard cart contents in session
		$order->calculateModifiers($force = true);
		//TO DO: HOW CAN THESE TWO BE DIFFERENT????
		if($order->Total() != $oldtotal) {
			$form->sessionMessage(_t('Order.PRICEUPDATED','The order price has been updated.'), 'warning');
			Director::redirectBack();
			return false;
		}
		// Create new OR update logged in {@link Member} record

		//MEMBER
		//TO DO: change to $form->saveInto($member)  = much better!
		$member = EcommerceRole::ecommerce_create_or_merge($data);
		
		if(!$member && Order::get_automatic_membership()) {
			$form->sessionMessage(
				_t(
					'Order.MEMBEREXISTS',
					'Sorry, an account with that email address already exists. If this is your email address, please log in first before placing your order.'
				),
				'bad'
			);
			Director::redirectBack();
			return false;
		}elseif($member){
			$member->write();
			$member->logIn();
		}

		// SHIPPING ADDRESS
		$shippingAddress = DataObject::get_one("ShippingAddress", "\"OrderID\" = ".$order->ID);
		if(!$shippingAddress){
			$shippingAddress = new ShippingAddress();
			$shippingAddress->OrderID = $order->ID;
		}
		if(isset($data['UseShippingAddress']) && $data['UseShippingAddress']){
			$form->saveInto($shippingAddress);
		}
		elseif($member){
			$shippingAddress->makeShippingAddressFromMember($member);
		}
		$shippingAddress->write();

		//ORDER
		//TODO: do we need this form saving into order - there are no fields in the form from the order...
		//saving customer note, UseShippingAddress, country...
		$form->saveInto($order);
		
		if($member){
			$order->MemberID = $member->ID;
		}
		
		$order->ShippingAddressID = $shippingAddress->ID;

		// IMPORTANT - SAVE ORDER....!
		$order->write();
		$order->tryToFinaliseOrder();

		//----------------- CLEAR OLD DATA ------------------------------
		$this->clearSessionData(); //clears the stored session form data that might have been needed if validation failed
		ShoppingCart::clear_order_from_shopping_cart();

		//----------------- PAYMENT ------------------------------
		return EcommercePayment::process_payment_form_and_return_next_step($order, $form, $data, $member);
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
		$this->clearMessage();
		Session::set("FormInfo.{$this->FormName()}.data", null);
	}

}


/**
 * @Description: allows customer to make additional payments for their order
 *
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class OrderForm_Validator extends ShopAccountForm_Validator{

	/**
	 * Ensures member unique id stays unique and other basic stuff...
	 * TODO: check if this code is not part of Member itself, as it applies to any member form.
	 */
	function php($data){
		$valid = parent::php($data);
		if(isset($data["ReadTermsAndConditions"])) {
			if(!$data["ReadTermsAndConditions"]) {
				$this->validationError(
					"ReadTermsAndConditions",
					_t("Order.READTERMSANDCONDITIONS", "Have you read the terms and conditions?"),
					"required"
				);
				$valid = false;
			}
		}
		if(!$valid) {
			$this->form->sessionMessage(_t("Order.ERRORINFORM", "We could not proceed with your order, please check your errors below."), "bad");
			$this->form->messageForForm("OrderForm", _t("Order.ERRORINFORM", "We could not proceed with your order, please check your errors below."), "bad");
		}
		return $valid;
	}

}



class OrderForm_Payment extends Form {

	function __construct($controller, $name, $order) {
		$fields = new FieldSet(
			new HiddenField('OrderID', '', $order->ID)
		);
		$totalAsCurrencyObject = $order->TotalAsCurrencyObject();
		$paymentFields = Payment::combined_form_fields($totalAsCurrencyObject->Nice());
		foreach($paymentFields as $paymentField) {
			if($paymentField->class == "HeaderField") {
				$paymentField->setTitle(_t("Order.MAKEPAYMENT", "Make Payment"));
			}
			$fields->push($paymentField);
		}
		$requiredFields = array();
		if($paymentRequiredFields = Payment::combined_form_requirements()) {
			$requiredFields = array_merge($requiredFields, $paymentRequiredFields);
		}
		$actions = new FieldSet(
			new FormAction('dopayment', _t('Order.PAYORDER','Pay outstanding balance'))
		);
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
	}

	function dopayment($data, $form) {
		$SQLData = Convert::raw2sql($data);
		$member = Member::currentUser();
		if($member && $orderID = intval($SQLData['OrderID'])) {
			$order = Order::get_by_id_and_member_id($orderID, $member->ID);
			if($order && $order->canPay()) {
				return EcommercePayment::process_payment_form_and_return_next_step($order, $form, $data);
			}
		}
		//to do - gracefull error
	}

}


/**
 * @Description: allows customer to cancel order.
 *
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/


class OrderForm_Cancel extends Form {

	function __construct($controller, $name, $order) {
		$fields = new FieldSet(
			new HiddenField('OrderID', '', $order->ID)
		);
		$actions = new FieldSet(
			new FormAction('docancel', _t('Order.CANCELORDER','Cancel this order'))
		);
		parent::__construct($controller, $name, $fields, $actions);
	}

	/**
	 * Form action handler for OrderForm_Cancel.
	 *
	 * Take the order that this was to be change on,
	 * and set the status that was requested from
	 * the form request data.
	 *
	 * @param array $data The form request data submitted
	 * @param Form $form The {@link Form} this was submitted on
	 */
	function docancel($data, $form) {
		$SQLData = Convert::raw2sql($data);
		$member = Member::currentUser();
		if(isset($SQLData['OrderID']) && $order = DataObject::get_one('Order', "\"ID\" = ".intval($SQLData['OrderID'])." AND \"MemberID\" = ".$member->ID)){
			if($order->canCancel()) {
				$order->CancelledByID = $member->ID;
				$order->write();
			}
			else {
				user_error("Tried to cancel an order that can not be cancelled with Order ID: ".$order->ID, "E_USER_NOTICE");
			}
		}
		//TODO: notify people via email??
		if($link = AccountPage::find_link()){
			AccountPage_Controller::set_message(_t("Order.ORDERHASBEENCANCELLED","Order has been cancelled"));
			Director::redirect($link);
		}
		else{
			Director::redirectBack();
		}
		return;
	}
}


