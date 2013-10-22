<?php

class ProductCategoryTest extends SapphireTest{
	
	static $fixture_file = 'shop/tests/fixtures/shop.yml';
	
	static $disable_theme = true;
	
	function setUp(){
		parent::setUp();
		ProductCategory::set_must_have_price(false);
		
		$this->products = $this->objFromFixture('ProductCategory','products');
		$this->products->publish('Stage','Live');
		$this->clothing = $this->objFromFixture('ProductCategory','clothing');
		$this->clothing->publish('Stage','Live');
		$this->electronics = $this->objFromFixture('ProductCategory','electronics');
		$this->electronics->publish('Stage','Live');
		
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
		$products = $this->products->ProductsShowable();		
		$this->assertNotNull($products,"Products exist in category");
		$this->assertDOSEquals(array(
			array('URLSegment' => 'socks'),
			array('URLSegment' => 't-shirt'),
			array('URLSegment' => 'hdtv'),
			array('URLSegment' => 'beach-ball'),
			//array('URLSegment' => 'mp3-player'), //music players category isn't published, therefore it shouldn't show up
		), $products);
	}

	function testSecondaryMembership(){
		$products = $this->electronics->ProductsShowable();
		$this->assertDOSEquals(array(
			array('URLSegment' => 'hdtv'),
//			array('URLSegment' => 'mp3-player'),
		), $products, 'Should initially contain only direct membership products');

		$this->socks->ProductCategories()->add($this->electronics);
		$this->socks->write();

		$products = $this->electronics->ProductsShowable();
		$this->assertDOSEquals(array(
			array('URLSegment' => 'hdtv'),
//			array('URLSegment' => 'mp3-player'),
			array('URLSegment' => 'socks'),
		), $products, 'After adding a category via many-many to socks, that should show up as well');
	}
	
	//TODO: check filtering
	//check published/ non published / allow purchase etc
	//Hide product if no price...or if product has variations, allow viewing.

}