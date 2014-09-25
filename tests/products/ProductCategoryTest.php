<?php

class ProductCategoryTest extends FunctionalTest{

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';
	public static $disable_theme = true;

	public function setUp() {
		parent::setUp();
		ProductCategory::config()->must_have_price = false;

		$this->products = $this->objFromFixture('ProductCategory', 'products');
		$this->products->publish('Stage', 'Live');
		$this->clothing = $this->objFromFixture('ProductCategory', 'clothing');
		$this->clothing->publish('Stage', 'Live');
		$this->electronics = $this->objFromFixture('ProductCategory', 'electronics');
		$this->electronics->publish('Stage', 'Live');

		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->socks->publish('Stage', 'Live');
		$this->tshirt = $this->objFromFixture('Product', 'tshirt');
		$this->tshirt->publish('Stage', 'Live');
		$this->hdtv = $this->objFromFixture('Product', 'hdtv');
		$this->hdtv->publish('Stage', 'Live');
		$this->beachball = $this->objFromFixture('Product', 'beachball');
		$this->beachball->publish('Stage', 'Live');
		$this->mp3player = $this->objFromFixture('Product', 'mp3player');
		$this->mp3player->publish('Stage', 'Live');

		Versioned::reading_stage('Live');
	}

	public function testCanViewProductCategoryPage() {
		$products = $this->objFromFixture('ProductCategory', 'products');
		$this->get(Director::makeRelative($products->Link()));
	}

	public function testGetAllProducts() {
		$products = $this->products->ProductsShowable();
		$this->assertNotNull($products, "Products exist in category");
		$this->assertDOSEquals(array(
			array('URLSegment' => 'socks'),
			array('URLSegment' => 't-shirt'),
			array('URLSegment' => 'hdtv'),
			array('URLSegment' => 'beach-ball'),
		), $products);
	}

	public function testSecondaryMembership() {
		$products = $this->electronics->ProductsShowable();
		$this->assertDOSEquals(array(
			array('URLSegment' => 'hdtv'),
		), $products, 'Should initially contain only direct membership products');

		$this->socks->ProductCategories()->add($this->electronics);
		$this->socks->write();

		$products = $this->electronics->ProductsShowable();
		$this->assertDOSEquals(array(
			array('URLSegment' => 'hdtv'),
			array('URLSegment' => 'socks'),
		), $products, 'After adding a category via many-many to socks, that should show up as well');
	}

	public function testFiltering() {
		$this->markTestIncomplete('check filtering');
		//check published/ non published / allow purchase etc
		//Hide product if no price...or if product has variations, allow viewing.
	}

}
