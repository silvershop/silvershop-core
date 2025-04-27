<?php

namespace SilverShop\Tests\Page;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Versioned\Versioned;

class ProductCategoryTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';
    public static bool $disable_theme = true;

    protected ProductCategory $products;
    protected ProductCategory $clothing;
    protected ProductCategory $electronics;
    protected Product $socks;
    protected Product $tshirt;
    protected Product $hdtv;
    protected Product $beachball;
    protected Product $mp3player;

    public function setUp(): void
    {
        parent::setUp();
        Config::modify()->set(ProductCategory::class, 'must_have_price', false);

        $this->logInWithPermission('ADMIN');

        $this->products = $this->objFromFixture(ProductCategory::class, 'products');
        $this->products->publishSingle();
        $this->clothing = $this->objFromFixture(ProductCategory::class, 'clothing');
        $this->clothing->publishSingle();
        $this->electronics = $this->objFromFixture(ProductCategory::class, 'electronics');
        $this->electronics->publishSingle();

        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->socks->publishSingle();
        $this->tshirt = $this->objFromFixture(Product::class, 'tshirt');
        $this->tshirt->publishSingle();
        $this->hdtv = $this->objFromFixture(Product::class, 'hdtv');
        $this->hdtv->publishSingle();
        $this->beachball = $this->objFromFixture(Product::class, 'beachball');
        $this->beachball->publishSingle();
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->mp3player->publishSingle();

        $this->logOut();

        Versioned::set_stage('Live');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCanViewProductCategoryPage(): void
    {
        $dataObject = $this->objFromFixture(ProductCategory::class, 'products');
        $this->get(Director::makeRelative($dataObject->Link()));
    }

    public function testGetAllProducts(): void
    {
        $dataList = $this->products->ProductsShowable();
        $this->assertNotNull($dataList, "Products exist in category");
        $this->assertListEquals(
            [
                ['URLSegment' => 'socks'],
                ['URLSegment' => 't-shirt'],
                ['URLSegment' => 'hdtv'],
                ['URLSegment' => 'beach-ball'],
            ],
            $dataList
        );
    }

    public function testSecondaryMembership(): void
    {
        $products = $this->electronics->ProductsShowable();
        $this->assertListEquals(
            [
                ['URLSegment' => 'hdtv'],
            ],
            $products,
            'Should initially contain only direct membership products'
        );

        $this->socks->ProductCategories()->add($this->electronics);
        $this->socks->write();

        $products = $this->electronics->ProductsShowable();
        $this->assertListEquals(
            [
                ['URLSegment' => 'hdtv'],
                ['URLSegment' => 'socks'],
            ],
            $products,
            'After adding a category via many-many to socks, that should show up as well'
        );
    }

    public function testZeroPrice(): void
    {
        Config::modify()->set(ProductCategory::class, 'must_have_price', true);

        $products = $this->products->ProductsShowable();
        $this->assertNotNull($products, "Products exist in category");
        // hdtv not in the list, since it doesn't have a base-price set
        $this->assertListEquals(
            [
                ['URLSegment' => 'socks'],
                ['URLSegment' => 't-shirt'],
                ['URLSegment' => 'beach-ball'],
            ],
            $products
        );

        $this->socks->BasePrice = '';
        $this->socks->write();

        $products = $this->products->ProductsShowable();
        $this->assertListEquals(
            [
                ['URLSegment' => 't-shirt'],
                ['URLSegment' => 'beach-ball'],
            ],
            $products
        );
    }

    public function testZeroPriceWithVariations(): void
    {
        Config::modify()->set(ProductCategory::class, 'must_have_price', true);

        $products = $this->electronics->ProductsShowable();
        $this->assertEquals(0, $products->count(), 'No product should be returned as there\'s no price set');

        // Create a variation for HDTV
        Variation::create()->update(
            [
                'InternalItemID' => '50-Inch',
                'Price' => 1200,
                'ProductID' => $this->hdtv->ID
            ]
        )->write();

        $products = $this->electronics->ProductsShowable();

        $this->assertListEquals(
            [
                ['URLSegment' => 'hdtv']
            ],
            $products,
            'HDTV has a priced extension and should now show up in the list of products'
        );
    }

    public function testFiltering(): void
    {
        // Test unpublished products
        $this->socks->doUnpublish();
        $products = $this->products->ProductsShowable();
        $this->assertNotContains(
            $this->socks->URLSegment,
            $products->column('URLSegment'),
            'Unpublished products should not be shown'
        );

        // Test AllowPurchase flag
        $this->tshirt->AllowPurchase = false;
        $this->tshirt->write();
        $products = $this->products->ProductsShowable();
        $this->assertContains(
            $this->tshirt->URLSegment,
            $products->column('URLSegment'),
            'Products with AllowPurchase=false are shown'
        );

        // Test products with no price but has variations
        Config::modify()->set(ProductCategory::class, 'must_have_price', true);
        $this->hdtv->BasePrice = 0;
        $this->hdtv->write();

        // Create variations for HDTV
        $variation1 = Variation::create(
            [
                'InternalItemID' => '42-Inch',
                'Price' => 899.99,
                'ProductID' => $this->hdtv->ID
            ]
        );
        $variation1->write();

        $variation2 = Variation::create(
            [
                'InternalItemID' => '55-Inch',
                'Price' => 1299.99,
                'ProductID' => $this->hdtv->ID
            ]
        );
        $variation2->write();

        $products = $this->products->ProductsShowable();
        $this->assertContains(
            $this->hdtv->URLSegment,
            $products->column('URLSegment'),
            'Products with no base price but with priced variations should be shown'
        );

        // Test products with no price and no variations
        $this->beachball->BasePrice = 0;
        $this->beachball->write();
        $products = $this->products->ProductsShowable();
        $this->assertNotContains(
            $this->beachball->URLSegment,
            $products->column('URLSegment'),
            'Products with no price and no variations should not be shown'
        );

        // Test draft products
        Versioned::set_stage('Stage');
        $newProduct = Product::create(
            [
                'Title' => 'Draft Product',
                'URLSegment' => 'draft-product',
                'BasePrice' => 99.99,
                'ParentID' => $this->products->ID
            ]
        );
        $newProduct->write();

        Versioned::set_stage('Live');
        $products = $this->products->ProductsShowable();
        $this->assertNotContains(
            'draft-product',
            $products->column('URLSegment'),
            'Draft products should not be shown in live mode'
        );

        // Test multiple filters combined
        $this->mp3player->AllowPurchase = false;
        $this->mp3player->BasePrice = 0;
        $this->mp3player->write();
        $products = $this->products->ProductsShowable();
        $this->assertNotContains(
            $this->mp3player->URLSegment,
            $products->column('URLSegment'),
            'Products failing multiple filter conditions should not be shown'
        );
    }
}
