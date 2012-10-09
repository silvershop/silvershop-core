<?php

class ProductCategoryTest extends SapphireTest{
	
	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	
	static $disable_theme = true;
	
	function setUp(){
		parent::setUp();
		ProductCategory::set_must_have_price(false);
		
		$this->cat1 = $this->objFromFixture('ProductCategory','g1');
		$this->cat1->publish('Stage','Live');
		$this->cat2 = $this->objFromFixture('ProductCategory','g2');
		$this->cat2->publish('Stage','Live');
		

		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->socks->publish('Stage','Live');
		
		$this->tshirt = $this->objFromFixture('Product', 'tshirt');
		$this->tshirt->publish('Stage','Live');
		$this->hdtv = $this->objFromFixture('Product', 'hdtv');
		$this->hdtv->publish('Stage','Live');
		
		
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->beachball->publish('Stage','Live');
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage','Live');
	}

	function testGetAllProducts(){
		
		$products = $this->cat1->ProductsShowable();
		$this->assertNotNull($products,"Products exist in category");
		$this->assertEquals($products->Count(),5,"Five products in category");
		
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