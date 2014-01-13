<?php

/**
 * Test product variation capabilities.
 *
 * @link ProductVariation
 * @link ProductVariationDecorator
 * @package shop
 * @subpackage tests
 */
class ProductVariationTest extends SapphireTest{
	
	static $fixture_file = 'shop/tests/fixtures/variations.yml';
	static $disable_theme = true;
	static $use_draft_site = true;
	
	function setUp(){
		parent::setUp();
		$this->ball = $this->objFromFixture("Product","ball");
		$this->mp3player = $this->objFromFixture("Product","mp3player");
		$this->redlarge = $this->objFromFixture("ProductVariation", "redlarge");
	}
	
	function testVariationOrderItem(){
		$cart = ShoppingCart::singleton();
		
		$emptyitem = $this->redlarge->Item();
		$this->assertEquals($emptyitem->Quantity,0);
		
		$cart->add($this->redlarge);
		$item = $cart->get($this->redlarge);
		$this->assertTrue((bool)$item,"item exists");
		$this->assertEquals(1, $item->Quantity);
		$this->assertEquals(22, $item->UnitPrice());
		$this->assertEquals("Size:Large, Color:Red", $item->SubTitle());

		
	}
	
	function testGetVaraition(){
		$colorred = $this->objFromFixture("ProductAttributeValue", "color_red");
		$sizelarge = $this->objFromFixture("ProductAttributeValue", "size_large");
		$attributes = array($colorred->ID, $sizelarge->ID);
		$variation = $this->ball->getVariationByAttributes($attributes);
		$this->assertTrue((bool)$variation, "Variation exists");
		$this->assertEquals(22, $variation->sellingPrice(),"Variation price is $22 (price of ball");
		
		$attributes = array($colorred->ID, 999);
		$variation = $this->ball->getVariationByAttributes($attributes);
		$this->assertFalse($variation,"Variation does not exist");
	}
	
	function testGenerateVariations(){
		$color = $this->objFromFixture("ProductAttributeType", "color");
		$values = array('Black','Blue'); //Note: black doesn't exist in the yaml
		$this->mp3player->generateVariationsFromAttributes($color,$values);
		
		$capacity = $this->objFromFixture("ProductAttributeType", "capacity");
		$values = array("120GB","300GB"); //Note: 300GB doesn't exist in the yaml
		$this->mp3player->generateVariationsFromAttributes($capacity,$values);
		
		$variations = $this->mp3player->Variations();
		$this->assertEquals($variations->Count(),4,"four variations created");
		
		//TODO: do a DOS match
	}
	
	//TODO: try bulk loading some variations ... generate, and exact entries
	
}