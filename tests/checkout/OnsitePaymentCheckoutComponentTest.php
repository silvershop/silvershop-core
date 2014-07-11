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

	/** @var CheckoutComponentConfig $config */
	protected $config;

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
	}

    public function testDefaultFields() {
        $fields = $this->config->getFormFields();
        $this->assertNull( $fields->fieldByName('SavedCreditCardID') );
        $this->assertNotNull( $fields->fieldByName('number') );
    }

    public function testSaveCreditCardsFlag() {
        Config::inst()->update('OnsitePaymentCheckoutComponent', 'save_credit_cards', false);
        $this->member->logIn();
        $this->makeTestSavedCard();
        $this->makeTestSavedCard();

        $fields = $this->config->getFormFields();
        $this->assertNull( $fields->fieldByName('SavedCreditCardID') );
        $this->assertNotNull( $fields->fieldByName('number') );
    }

    public function testSavedCards() {
        $this->member->logIn();
        $card1 = $this->makeTestSavedCard();
        $card2 = $this->makeTestSavedCard();

        $fields = $this->config->getFormFields();
        $fields = $fields->first()->getChildren(); // when existing fields are present, this is wrapped in a composite field
        $this->assertNotNull( $fields->fieldByName('number') );

        /** @var DropdownField $dd */
        $dd = $fields->fieldByName('SavedCreditCardID');
        $this->assertNotNull($dd);
        $this->assertEquals(3, count($dd->getSource()));
        $this->assertArrayHasKey('newcard', $dd->getSource());
        $this->assertArrayHasKey($card1->ID, $dd->getSource());
        $this->assertArrayHasKey($card2->ID, $dd->getSource());
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