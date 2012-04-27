<?php

class ProductCategoryTest extends SapphireTest{
	
	static $fixture_file = 'shop/tests/shop.yml';
	
	static $disable_theme = true;
	
	function setUp(){
		parent::setUp();
		ProductCategory::set_must_have_price(false);
		
		$this->g1 = $this->objFromFixture('ProductCategory','g1');
		$this->g2 = $this->objFromFixture('ProductCategory','g2');
		
	}

	function testGetAllProducts(){
		
		$products = $this->g1->ProductsShowable();
		$this->assertEquals($products->Count(),5);
		
		$this->assertDOSEquals(array(
			array('URLSegment' => 'socks'),
			array('URLSegment' => 't-shirt'),
			array('URLSegment' => 'hdtv'),
			array('URLSegment' => 'beach-ball'),
			array('URLSegment' => 'mp3-player'),
		), $products);
		
	}
	
	//TODO: check that sub-category products show up
	//TODO: check filtering
	
	//check published/ non published / allow purchase etc
	
	//Hide product if no price...or if product has variations, allow viewing.

}