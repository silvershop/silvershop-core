<?php

namespace SilverShop\Tests\Admin;

use SilverShop\Admin\ProductBulkLoader;
use SilverShop\Page\Product;
use SilverStripe\Dev\FunctionalTest;

class ProductBulkLoaderTest extends FunctionalTest
{
    public static $fixture_file   = __DIR__ . '/../Fixtures/shop.yml';
    public static $disable_theme  = true;
    protected static $use_draft_site = true;

    public function testLoad()
    {
        $loader = new ProductBulkLoader(Product::class);

        $ds = DIRECTORY_SEPARATOR;
        $filepath = realpath(__DIR__ . $ds . 'test_products.csv');
        $file = fopen($filepath, 'r');

        fgetcsv($file); // pop header row
        $compareRow = fgetcsv($file);
        $results = $loader->load($filepath);

        // Test that right amount of columns was imported
        //$this->assertEquals(4, $results->Count(), 'Test correct count of imported data');

        // Test that columns were correctly imported
        $obj = Product::get()->filter('Title', 'Socks')->first();
        $this->assertNotNull($obj, "New product exists");
        $this->assertEquals("<p>The comfiest pair of socks you'll ever own.</p>", $obj->Content, "Content matches");
        $this->assertEquals(12, $obj->BasePrice, "Checking price matches.");
        //$this->assertEquals(124, $obj->ID,"Checking ID matches");

        fclose($file);

        $this->markTestIncomplete('Incomplete');
    }
}
