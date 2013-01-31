<?php

class SteppedCheckoutTest extends FunctionalTest{
	
	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	
	public static $use_draft_site = true; //so we don't need to publish
	
	protected $autoFollowRedirection = false;
	
	function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
		//set up steps
		SteppedCheckout::setupSteps(); //use default steps
		
		$this->socks = $this->objFromFixture("Product", "socks");
		$this->socks->publish('Stage','Live');
		
		$this->checkout = new CheckoutPage_Controller($this->objFromFixture("CheckoutPage", "checkout"));
		$this->checkout->handleRequest(new SS_HTTPRequest("GET", "checkout"), DataModel::inst());
		
		$this->cart = $this->objFromFixture("Order", "cart");
		ShoppingCart::singleton()->setCurrent($this->cart);
	}
	
	function testTemplateFunctions(){
		$this->checkout->handleRequest(new SS_HTTPRequest('GET', ""), DataModel::inst()); //put us at the first step index == membership
		$this->assertFalse($this->checkout->IsPastStep('membership'));
		$this->assertTrue($this->checkout->IsCurrentStep('membership'));
		$this->assertFalse($this->checkout->IsFutureStep('membership'));
		
		$this->checkout->NextStepLink(); //TODO: assertion
		
		$this->assertFalse($this->checkout->IsPastStep('contactdetails'));
		$this->assertFalse($this->checkout->IsCurrentStep('contactdetails'));
		$this->assertTrue($this->checkout->IsFutureStep('contactdetails'));
		
		$this->checkout->handleRequest(new SS_HTTPRequest('GET', "summary"), DataModel::inst()); //change to summary step
		$this->assertFalse($this->checkout->IsPastStep('summary'));
		$this->assertTrue($this->checkout->IsCurrentStep('summary'));
		$this->assertFalse($this->checkout->IsFutureStep('summary'));
		
	}
	
	function testMembershipStep(){
		$this->checkout->index();
		$this->checkout->membership();
		$this->post('/checkout/guestcontinue', array()); //redirect to next step
		$form = $this->checkout->MembershipForm();
		$data = array();
		$form->loadDataFrom($data);
		$this->checkout->createaccount(new SS_HTTPRequest('GET', "/checkout/createaccount")); //redirect to create checkout
	
		$data = array(
			'FirstName' => 'Michael',
			'Surname' => 'Black',
			'Email' => 'mb@blahmail.com',
			'Password' => array(
				'_Password' => 'pass1234',
				'_ConfirmPassword' => 'pass1234'
			),
			'action_docreateaccount' => 'Create New Account'
		);
		$response = $this->post('/checkout/CreateAccountForm', $data); //redirect to next step
		//TODO: check redirect?
		//check new account was created
		$member = ShopMember::get_by_identifier("mb@blahmail.com");
		$this->assertEquals($member->FirstName,'Michael');
		$this->assertEquals($member->Surname,'Black');
	}
	
	function testContactDetails(){
		$this->objFromFixture("Member", "joebloggs")->logIn();
		$this->checkout->contactdetails();
		$data = array(
			'FirstName' => 'Pauline',
			'Surname' => 'Richardson',
			'Email' => 'p.richardson@mail.co',
			'action_setcontactdetails' => 1	
		);
		$response = $this->post('/checkout/ContactDetailsForm', $data);
		//TODO: check order has been updated
	}
	
	function testShippingAddress(){
		$this->objFromFixture("Member", "joebloggs")->logIn();
		$this->checkout->shippingaddress();
		$data = array(
			'Address' => '2b Baba place',
			'AddressLine2' => 'Level 2',
			'City' => 'Newton',
			'State' => 'Wellington',
			'Country' => 'NZ',
			'action_setaddress' => 1	
		);
		$response = $this->post('/checkout/AddressForm', $data);
	}
	
	function testBillingAddress(){
		$this->objFromFixture("Member", "joebloggs")->logIn();
		$this->checkout->billingaddress();
		$data = array(
			'Address' => '3 Art Cresent',
			'AddressLine2' => '',
			'City' => 'Walkworth',
			'State' => 'New Caliphoneya',
			'Country' => 'ZA',
			'action_setbillingaddress' => 1	
		);
		$response = $this->post('/checkout/AddressForm', $data);
	}
	
	function testPaymentMethod(){
		//$this->checkout->paymentmethod(); //doesn't work, because a redirect occurrs if there is only 1 payment type
		$data = array(
			'PaymentMethod' => 'Cheque',
			'action_setpaymentmethod' => 1
		);
		$response = $this->post('/checkout/PaymentMethodForm', $data);
		$this->assertEquals($this->checkout->getSelectedPaymentMethod(), 'Cheque');
	}
	
	function testSummary(){
		$this->checkout->summary();
		$form = $this->checkout->ConfirmationForm();
		$data = array(
			'Notes' => 'Leave it around the back',
			'ReadTermsAndConditions' => 1,
			'PaymentMethod' => 'Cheque',
			'action_place' => "Confirm and Pay"
		);
		Checkout::get($this->cart)->setPaymentMethod("Cheque"); //a selected payment method is required
		$form->loadDataFrom($data);
		$this->assertTrue($form->validate(),"Checkout data is valid");		
		$response = $this->post('/checkout/ConfirmationForm', $data);
		$this->assertEquals($this->cart->Status,'Unpaid', "Order status is updated");
	}
	
}