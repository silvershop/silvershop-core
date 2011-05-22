<?php
/**
 * Test {@link CheckoutPage}
 * 
 * @package ecommerce
 */
class CheckoutPageTest extends FunctionalTest {
	
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	
	static $disable_theme = true;
	
	static $use_draft_site = true;


	function testFindLink() {
		$link = CheckoutPage::find_link();
		$this->assertEquals(Director::baseURL() . 'checkout/', $link, 'find_link() returns the correct link to checkout.');

		/* If there is no checkout page, then an exception is thrown */
		$page = DataObject::get_one('CheckoutPage');
		$page->delete();
		$page->flushCache();
		
		$this->setExpectedException('Exception');
		$link = CheckoutPage::find_link();
	}
	
	/* --- OLD TESTS (to be removed) -- */
	
	/**
	 * CheckOUT unit tests need to be rewritten to work with the new shopping card implementation.
	 */
	function old_testCheckout() {
			/* Add a couple of items to the cart */
		$this->get('product-1b/add');
		$this->get('product-1b/add');
		$this->get('product-2a/add');
		
		/* Check the cart */
		$this->get('checkout/');

		/** 
		 * R.Spittel
		 * @todo update to be able to do the test on the new structure.
		$this->assertExactMatchBySelector('#InformationTable tr.orderitem td.product a', array(
			'Product 1b',
			'Product 2a'
		));
		*/ 
		
		/* the HTML tags aren't consistently output at this stage
		$this->assertExactHTMLMatchBySelector('#InformationTable tr.orderitem td.quantity input.ajaxQuantityField', array(
			'<input name="ProductOrderItem_0_Quantity" class="ajaxQuantityField" type="text" value="1" size="3" maxlength="3" disabled="disabled"/>',
			'<input name="ProductOrderItem_1_Quantity" class="ajaxQuantityField" type="text" value="2" size="3" maxlength="3" disabled="disabled"/>',
		));
		*/
		
		/** 
		 * R.Spittel
		 * @todo update to be able to do the test on the new structure.
		
		$this->assertExactMatchBySelector('#InformationTable tr.orderitem td.total', array(
			'$1,200.00',
			'$800.00',
		));

		$this->assertExactMatchBySelector('#Table_Order_SubTotal', array(
			'$2,000.00',
		));
		*/
	}
	
}
?>