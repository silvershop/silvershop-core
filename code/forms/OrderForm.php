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

	function __construct($controller, $name) {
		//Requirements::themedCSS('OrderForm');

		// 1) Member and shipping fields
		$member = Member::currentUser() ? Member::currentUser() : singleton('Member');

		$memberFields = new CompositeField($member->getEcommerceFields());

		$requiredFields = $member->getEcommerceRequiredFields();

		if(ShoppingCart::uses_different_shipping_address()) {
			$countryField = new DropdownField('ShippingCountry', 'Country', Geoip::getCountryDropDown(), EcommerceRole::findCountry());
			$shippingFields = new CompositeField(
				new HeaderField('Send goods to different address', 3),
				new LiteralField('ShippingNote', '<p class="message warning">Your goods will be sent to the address below.</p>'),
				new LiteralField('Help', '<p>You can use this for gift giving. No billing information will be disclosed to this address.</p>'),
				new TextField('ShippingName', 'Name'),
				new TextField('ShippingAddress', 'Address'),
				new TextField('ShippingAddress2', ''),
				new TextField('ShippingCity', 'City'),
				$countryField,
				new HiddenField('UseShippingAddress', '', true),
				$changeshippingbutton = new FormAction_WithoutLabel('useMemberShippingAddress', 'Use Billing Address for Shipping')
			);
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			$changeshippingbutton->setButtonContent('Use Billing Address for Shipping');
			$changeshippingbutton->useButtonTag = true;

			$requiredFields[] = 'ShippingName';
			$requiredFields[] = 'ShippingAddress';
			$requiredFields[] = 'ShippingCity';
			$requiredFields[] = 'ShippingCountry';
		} else {
			$countryField = $memberFields->fieldByName('Country');
			$shippingFields = new FormAction_WithoutLabel('useDifferentShippingAddress', 'Use Different Shipping Address');
			//Need to to this because 'FormAction_WithoutLabel' has no text on the actual button
			$shippingFields->setButtonContent('Use Different Shipping Address');
			$shippingFields->useButtonTag = true;
		}

		$countryField->addExtraClass('ajaxCountryField');

		$setCountryLinkID = $countryField->id() . '_SetCountryLink';
		$setContryLink = ShoppingCart_Controller::set_country_link();
		$memberFields->push(new HiddenField($setCountryLinkID, '', $setContryLink));

		$leftFields = new CompositeField($memberFields, $shippingFields);
		$leftFields->setID('LeftOrder');

		$rightFields = new CompositeField();
		$rightFields->setID('RightOrder');

		if(!$member->ID || $member->Password == '') {
			$rightFields->push(new HeaderField('Membership Details', 3));
			$rightFields->push(new LiteralField('MemberInfo', "<p class=\"message good\">If you are already a member, please <a href=\"Security/login?BackURL=" . CheckoutPage::find_link(true) . "/\">log in</a>.</p>"));
			$rightFields->push(new LiteralField('AccountInfo', "<p>Please choose a password, so you can login and check your order history in the future.</p><br/>"));
			$rightFields->push(new FieldGroup(new ConfirmedPasswordField('Password', 'Password')));

			$requiredFields[] = 'Password[_Password]';
			$requiredFields[] = 'Password[_ConfirmPassword]';
		}

		// 2) Payment fields
		$currentOrder = ShoppingCart::current_order();
		$total = '$' . number_format($currentOrder->Total(), 2);//TODO: make this multi-currency
		$paymentFields = Payment::combined_form_fields("$total " . $currentOrder->Currency(), $currentOrder->Total());
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
		$actions = new FieldSet(new FormAction('processOrder', 'Place order and make payment'));
		$requiredFields = new CustomRequiredFields($requiredFields);
		$this->extend('updateValidator',$requiredFields);

		$this->extend('updateFields',&$fields);

		// 6) Form construction
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);

		// 7) Member details loading
		if($member->ID) $this->loadDataFrom($member);

		// 8) Country field value update
		$currentOrder = ShoppingCart::current_order();
		$currentOrderCountry = $currentOrder->findShippingCountry(true);
		$countryField->setValue($currentOrderCountry);

		//allow updating via decoration
		$this->extend('updateForm',$this);
	}

	/**
	 * Disable the validator when the action clicked is to use a different shipping address
	 * or use the member shipping address.
	 */
	function beforeProcessing() {
		if(isset($_REQUEST['action_useDifferentShippingAddress']) || isset($_REQUEST['action_useMemberShippingAddress'])) return true;
		else return parent::beforeProcessing();
	}

	/**
	 * Save in the session that the current member wants to use a different shipping address.
	 */
	function useDifferentShippingAddress($data, $form, $request) {
		ShoppingCart::set_uses_different_shipping_address(true);
		Director::redirectBack();
	}

	/**
	 * Save in the session that the current member wants to use his address as a shipping address.
	 */
	function useMemberShippingAddress($data, $form, $request) {
		ShoppingCart::set_uses_different_shipping_address(false);
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
			user_error(get_class($payment) . ' is not a valid Payment object!', E_USER_ERROR);
		}

		if(!ShoppingCart::has_items()) {
			$form->sessionMessage('Please add some items to your cart', 'bad');
			Director::redirectBack();
			return false;
		}

		// Create new OR update logged in {@link Member} record
		$member = EcommerceRole::createOrMerge($data);
		if(!$member) {
			$form->sessionMessage(
				_t(
					'OrderForm.MEMBEREXISTS', 'Sorry, a member already exists with that email address.
					If this is your email address, please log in first before placing your order.'
				),
				'bad'
			);

			Director::redirectBack();
			return false;
		}

		$member->write();
		$member->logIn();

		if($member)	$payment->PaidByID = $member->ID;

		// Create new Order from shopping cart, discard cart contents in session
		$order = ShoppingCart::save_current_order();
		ShoppingCart::clear();

		// Write new record {@link Order} to database
		$form->saveInto($order);
		$order->write();

		// Save payment data from form and process payment
		$form->saveInto($payment);
		$payment->OrderID = $order->ID;
		$payment->Amount->Amount = $order->Total();
		$payment->Amount->Currency = Order::Currency();
		$payment->write();

		//prepare $data

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

	/** Override form validation to make different shipping address button work */
	 function validate(){
		if(!isset($_POST['action_processOrder'])  && (isset($_POST['action_useMemberShippingAddress']) || isset($_POST['action_useDifferentShippingAddress']) ) ){
			return true;
		}
		return parent::validate();
	 }

}
