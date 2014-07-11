<?php

class OnsitePaymentCheckoutComponentTest extends SapphireTest {

	protected static $fixture_file = array(
		'shop/tests/fixtures/Orders.yml',
		'shop/tests/fixtures/ShopMembers.yml',
	);

	/** @var Order $cart */
	protected $cart;

	/** @var Member $member */
	protected $member;

    /** @var SavedCreditCard $card */
    protected $card;

	/** @var CheckoutComponentConfig $config */
	protected $config;

    protected $fixtureNewCard = array(
        'name'        => 'Joe Bloggs',
        'number'      => '4242424242424242',
        'cvv'         => 123,
        'expiryMonth' => 7,
        'expiryYear'  => 2099,
    );

	public function setUp() {
		ShopTest::setConfiguration();
		CheckoutConfig::config()->membership_required = false;
        Config::inst()->remove('Payment', 'allowed_gateways');
        Config::inst()->update('Payment', 'allowed_gateways', array('Stripe'));
        Config::inst()->update('OnsitePaymentCheckoutComponent', 'save_credit_cards', true);
		parent::setUp();

		$this->member   = $this->objFromFixture("Member", "jeremyperemy");
		$this->cart     = $this->objFromFixture("Order", "cart1");
		$this->config   = new CheckoutComponentConfig($this->cart, false);
		$this->config->addComponent( new OnsitePaymentCheckoutComponent() );
        $this->member->logIn();
        $this->makeTestSavedCard();
        $this->card = $this->makeTestSavedCard();
	}

    public function testDefaultFields() {
        $this->member->logOut();

        $fields = $this->config->getFormFields();
        $this->assertNull( $fields->fieldByName('SavedCreditCardID') );
        $this->assertNotNull( $fields->fieldByName('number') );
    }

    public function testFieldsWithoutSaveCreditCardsFlag() {
        Config::inst()->update('OnsitePaymentCheckoutComponent', 'save_credit_cards', false);

        $fields = $this->config->getFormFields();
        $this->assertNull( $fields->fieldByName('SavedCreditCardID') );
        $this->assertNotNull( $fields->fieldByName('number') );
    }

    public function testFieldsWithSavedCards() {
        $fields = $this->config->getFormFields();
        $fields = $fields->first()->getChildren(); // when existing fields are present, this is wrapped in a composite field
        $this->assertNotNull( $fields->fieldByName('number') );
        /** @var DropdownField $dd */
        $dd = $fields->fieldByName('SavedCreditCardID');
        $this->assertNotNull($dd);
        $this->assertEquals(3, count($dd->getSource()));
        $this->assertArrayHasKey('newcard', $dd->getSource());
        $this->assertArrayHasKey($this->card->ID, $dd->getSource());
    }

    public function testCreateNewCard() {
        $this->assertTrue(
            $this->config->validateData($this->fixtureNewCard)
        );
    }

    public function testUseExistingCard() {
        $this->assertTrue(
            $this->config->validateData(array(
                'SavedCreditCardID' => $this->card->ID
            ))
        );
    }

    public function testIncompleteCard() {
        $data = array_splice($this->fixtureNewCard, 0);
        $data['number'] = '';

        $this->setExpectedException('ValidationException');
        $this->config->validateData($data);
    }

    public function testShouldRejectExistingIfNotLoggedIn() {
        $this->member->logOut();

        $this->setExpectedException('ValidationException');
        $this->config->validateData(array(
            'SavedCreditCardID' => $this->card->ID
        ));
    }

    public function testShouldRejectExistingIfNotOwnedByMember() {
        $this->card->UserID = 0;
        $this->card->write();

        $this->setExpectedException('ValidationException');
        $this->config->validateData(array(
            'SavedCreditCardID' => $this->card->ID
        ));
    }

    protected function makeTestSavedCard() {
        $card = new SavedCreditCard();
        $card->CardReference = uniqid('TEST');
        $card->LastFourDigits = substr($card->CardReference, -4);
        $card->UserID = $this->member->ID;
        $card->write();
        return $card;
    }

}