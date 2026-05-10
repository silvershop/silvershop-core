<?php

declare(strict_types=1);

namespace SilverShop\Tests\Admin;

use SilverShop\Admin\ProductBulkLoader;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;

final class ProductBulkLoaderTest extends FunctionalTest
{
    public static $fixture_file   = __DIR__ . '/../Fixtures/shop.yml';

    public static $disable_theme  = true;

    protected static $use_draft_site = true;

    public function testLoad(): void
    {
        $productBulkLoader = ProductBulkLoader::create(Product::class);

        $ds = DIRECTORY_SEPARATOR;
        $filepath = realpath(__DIR__ . $ds . 'test_products.csv');
        $file = fopen($filepath, 'r');

        fgetcsv($file, null, ",", '"', ""); // pop header row
        fgetcsv($file, null, ",", '"', "");
        $results = $productBulkLoader->load($filepath);

        // Test that right amount of columns was imported
        $this->assertEquals(13, $results->Count(), 'Test correct count of imported data');

        // Test that columns were correctly imported
        $obj = Product::get()->filter(['Title' => 'Socks'])->first();
        $this->assertNotNull($obj, "New product exists");
        $this->assertEquals("<p>The comfiest pair of socks you'll ever own.</p>", $obj->Content, "Content matches");
        $this->assertEquals(12, $obj->BasePrice, "Checking price matches.");
        $this->assertEquals(124, $obj->InternalItemID, "Checking ID matches");
        fclose($file);
    }

    public function testCreateNewProductGroupWhenConfigured(): void
    {
        Config::modify()->set(ProductBulkLoader::class, 'create_new_product_groups', true);
        try {
            $productBulkLoader = ProductBulkLoader::create(Product::class);
            $product = $this->objFromFixture(Product::class, 'socks');
            $newCategoryTitle = 'Category Created By Bulk Loader';

            $productBulkLoader->setParent($product, $newCategoryTitle);

            $createdCategory = ProductCategory::get()->filter('Title', strtolower($newCategoryTitle))->first();
            $this->assertNotNull($createdCategory);
            $this->assertSame((int) $createdCategory->ID, (int) $product->ParentID);
        } finally {
            Config::modify()->set(ProductBulkLoader::class, 'create_new_product_groups', false);
        }
    }
}
