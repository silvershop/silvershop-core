<?php

class ProductBulkLoaderTest extends FunctionalTest {

	public static $fixture_file = 'shop/tests/fixtures/shop.yml';
	public static $disable_theme = true;
	public static $use_draft_site = true;

	public function testLoad() {
		$loader = new ProductBulkLoader('Product');

		$filepath = Director::baseFolder() . '/'.SHOP_DIR.'/tests/test_products.csv';
		$file = fopen($filepath, 'r');

		fgetcsv($file); // pop header row
		$compareRow = fgetcsv($file);
		$results = $loader->load($filepath);

		// Test that right amount of columns was imported
		//$this->assertEquals(4, $results->Count(), 'Test correct count of imported data');

		// Test that columns were correctly imported
		$obj = DataObject::get_one("Product", "\"Title\" = 'Socks'");
		$this->assertNotNull($obj, "New product exists");
		$this->assertEquals("<p>The comfiest pair of socks you'll ever own.</p>", $obj->Content, "Content matches");
		$this->assertEquals(12, $obj->BasePrice, "Checking price matches.");
		//$this->assertEquals(124, $obj->ID,"Checking ID matches");

		fclose($file);

		$this->markTestIncomplete('Incomplete');
	}

}
