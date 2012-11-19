<?php

class OrderFormTest extends SapphireTest{
	
	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	
	function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage','Live');
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->socks->publish('Stage','Live');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->beachball->publish('Stage','Live');
		
		$this->checkoutcontroller = new CheckoutPage_Controller();
		
		ShoppingCart::singleton()->add($this->socks); //start cart
	}
	
	function testDefaultOrderForm(){
		$form = new OrderForm($this->checkoutcontroller, "OrderForm");
		
		//TODO: make assertions
	}
	
	function testSeperateBillingForm(){
		$order = ShoppingCart::curr();
		//seperate billing address
		$order->SeparateBillingAddress = true;
		$form = new OrderForm($this->checkoutcontroller, "OrderForm");
		
		//TODO: make assertions
	}
	
	function testMemberForm(){
		//log in a member
		$this->objFromFixture("Member", "joebloggs")->logIn();
		$form = new OrderForm($this->checkoutcontroller, "OrderForm");
		
		//TODO: make assertions
	}
	
	function testProcessOrder(){
		//TODO
	}
	
}