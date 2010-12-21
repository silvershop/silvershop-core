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

		Requirements::javascript('ecommerce/javascript/OrderForm.js');

		// 1) Member fields
		$member = Member::currentUser() ? Member::currentUser() : singleton('Member');

		$memberFields = new CompositeField($member->getEcommerceFields());

		$requiredFields = $member->getEcommerceRequiredFields();

		$leftFields = new CompositeField($memberFields);
		$leftFields->setID('LeftOrder');

		$rightFields = new CompositeField();
		$rightFields->setID('RightOrder');

		if(!$member || !$member->ID || $member->Password == '') {
			//login invite right on the top
			$rightFields->push(new HeaderField(_t('OrderForm.MembershipDetails','Membership Details'), 3));
			$rightFields->push(new LiteralField('MemberInfo', '<p class="message good">'._t('OrderForm.MemberInfo','If you are already a member please')." <a href=\"Security/login?BackURL=" . CheckoutPage::find_link(true) . "/\">"._t('OrderForm.LogIn','log in').'</a>.</p>'));
			$rightFields->push(new LiteralField('AccountInfo', '<p>'._t('OrderForm.AccountInfo','Please choose a password, so you can login and check your order history in the future').'</p><br/>'));
			$rightFields->push(new FieldGroup(new ConfirmedPasswordField('Password', _t('OrderForm.Password','Password'))));
			$requiredFields[] = 'Password[_Password]';
			$requiredFields[] = 'Password[_ConfirmPassword]';
		}


		// 2) Payment fields
		$currentOrder = ShoppingCart::current_order();
		$totalobj = DBField::create('Currency',$currentOrder->Total()); //should instead be $totalobj = $currentOrder->dbObject('Total');
		$paymentFields = Payment::combined_form_fields($totalobj->Nice());
		foreach($paymentFields as $field) {
			$rightFields->push($field);
		}
		if($paymentRequiredFields = Payment::combined_form_requirements()) {
			$requiredFields = array_merge($requiredFields, $paymentRequiredFields);
		}

		// 3) Put all the fields in one FieldSet
		$fields = new FieldSet($leftFields, $rightFields);

		// 4) Terms and conditions field
		// If a terms and conditions page exists, we need to create a field to confirm the user has read it
		if($controller->TermsPageID && $termsPage = DataObject::get_by_id('Page', $controller->TermsPageID)) {
			$bottomFields = new CompositeField(new CheckboxField('ReadTermsAndConditions', "I agree to the terms and conditions stated on the <a href=\"$termsPage->URLSegment\" title=\"Read the shop terms and conditions for this site\">terms and conditions</a> page"));
			$bottomFields->push(new TextareaField('CustomerOrderNote', 'Note / Question', 7, 30));
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
		if($member->ID) $this->loadDataFrom($member);

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
		$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
		$payment = class_exists($paymentClass) ? new $paymentClass() : null;

		if(!($payment && $payment instanceof Payment)) {
			user_error(get_class($payment) . ' is not a valid Payment object!', E_USER_ERROR);
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


		// Create new OR update logged in {@link Member} record
		$member = EcommerceRole::ecommerce_create_or_merge($data);
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

		// Write new record {@link Order} to database
		$form->saveInto($order);
		$order->save(); //sets status to 'Unpaid'
		$order->MemberID = $member->ID;
		$order->write();


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
