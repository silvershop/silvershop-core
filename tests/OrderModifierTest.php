<?php
/**
 * @package ecommerce
 * @subpackage tests
 */

class OrderModifierTest extends FunctionalTest {
	
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;
	static $use_draft_site = true;
	
	function setUp() {
		parent::setUp();
		
		/*
		// Set the modifiers to test
		Order::set_modifiers(array(
			'SimpleShippingModifier',
			'TaxModifier'
		));
		
		// Set the tax configuration on a per-country basis to test
		TaxModifier::set_for_country('NZ', 0.125, 'GST', 'inclusive');
		TaxModifier::set_for_country('UK', 0.175, 'VAT', 'exclusive');
		
		// Let's check that we have the Payment module installed properly
		$this->assertTrue(class_exists('Payment'), 'Payment module is installed.');
		
		// Set the site currency to NZD - this updates all the pricing labels from USD to NZD 
		Payment::set_site_currency('NZD');
		
		// Set up the simple shipping calculator to test
		SimpleShippingModifier::set_default_charge(10);
		SimpleShippingModifier::set_charges_for_countries(array(
			'NZ' => 5,
			'UK' => 20
		));
		*/
	}
	
	function tearDown(){
		parent::tearDown();
		
		//get rid of all lingering modifiers, order items etc
		if($attributes = DataObject::get('OrderAttribute')){
			foreach($attributes as $attribute){
				$attribute->delete();
				$attribute->destroy();
			}
		}
	}
	
	
	function testModiferCreation(){
		
		//TODO:
		//place an item in cart
		//check modifiers are also setup with order
		//check modifiers change after new items are added
		//place order
		//check modifers are still correct
	}
	
	
	/* -------------- OLD TESTS (to be removed) -------------------- */
	
	function old_testTaxModifier() {
		$product1b = $this->objFromFixture('Product', 'p1b');
		
		// Add 2 of the product-1b to the shopping cart
		$this->get($product1b->addLink());
		$this->get($product1b->addLink());

		// Log our NZ member in so we can assert they see the GST component
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		
		// 12.5% GST appears to our NZ user logged in
		$response = $this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.taxmodifier td', array(
			'12.5% GST (included in the above price)'
		));

		// Let's check the totals to make sure GST wasn't being added (which is important!)
		// NZD is shown as the label, since it was set as the site currency in setUp()
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,205.00');
		
		// Let's sneakily change the GST to be exclusive, altering the checkout total
		TaxModifier::set_for_country('NZ', 0.125, 'GST', 'exclusive');
		
		// See what the checkout page has got now
		$this->get('checkout/');
		
		// Check the total, it has changed since the GST is now exclusive
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,355.63');
				
		// Member logs out
		$this->session()->inst_set('loggedInAs', null);
	}
	
	function old_testSimpleShippingModifier() {
		$product1b = $this->objFromFixture('Product', 'p1b');
		
		// Add 2 of the product-1b to the shopping cart
		$this->get($product1b->addLink());
		$this->get($product1b->addLink());

		// Initially, 10 should be charged for everyone
		$this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.simpleshippingmodifier td', array(
			'$10.00'
		));
		
		// Check the total is correct
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,210.00');
		
		// Log in an NZ member in so we can assert a different price set for NZ customers
		$this->session()->inst_set('loggedInAs', $this->idFromFixture('Member', 'member'));
		
		// 5 is now charged, because we are logged in with a member from NZ
		$this->get('checkout/');
		$this->assertPartialMatchBySelector('tr.simpleshippingmodifier td', array(
			'$5.00'
		));
		
		// Check the total was updated with the change in shipping applied
		$this->assertExactMatchBySelector('#Table_Order_Total', '$1,205.00');
		
		// Member logs out
		$this->session()->inst_set('loggedInAs', null);
	}
	
}

?>