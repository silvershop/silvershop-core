<?php

class ShopToolsTest extends SapphireTest{
	
	function setUp(){
		parent::setUp();
		ShopTest::setConfiguration();
	}
	
	function testPriceForDisplay(){
		$dp = ShopTools::price_for_display(12345.67);
		$this->assertEquals($dp->Nice(),"$12,345.67");
	}
	
}