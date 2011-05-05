<?php

class ProductBulkLoaderTest extends FunctionalTest {
	
	static $fixture_file = 'ecommerce/tests/ecommerce.yml';
	static $disable_theme = true;
	static $use_draft_site = true;
	
	function testLoad() {
		$loader = new ProductBulkLoader('Product');
		
		$filepath = Director::baseFolder() . '/ecommerce/tests/test_products.csv';
		$file = fopen($filepath, 'r');

		fgetcsv($file); // pop header row
		$compareRow = fgetcsv($file);
		$results = $loader->load($filepath);
	
		// Test that right amount of columns was imported
		//$this->assertEquals(4, $results->Count(), 'Test correct count of imported data');
		
		// Test that columns were correctly imported
		$obj = DataObject::get_one("Product", "\"Title\" = 'Socks'");
		$this->assertNotNull($obj);
		$this->assertEquals("<p>The comfiest pair of socks you'll ever own.</p>", $obj->Content);
		$this->assertEquals(12, $obj->Price,"Checking price matches.");
		//$this->assertEquals(124, $obj->ID,"Checking ID matches");
		
		fclose($file);
	}
	
	
}