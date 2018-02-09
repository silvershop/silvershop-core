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
    public static $disable_theme = true;

    /**
     * @var ProductCategory
     */
    protected $products;

    /**
     * @var ProductCategory
     */
    protected $clothing;

    /**
     * @var ProductCategory
     */
    protected $electronics;

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Product
     */
    protected $tshirt;

    /**
     * @var Product
     */
    protected $hdtv;

    /**
     * @var Product
     */
    protected $beachball;

    /**
     * @var Product
     */
    protected $mp3player;

    public function setUp()
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

    public function testCanViewProductCategoryPage()
    {
        $products = $this->objFromFixture(ProductCategory::class, 'products');
        $this->get(Director::makeRelative($products->Link()));
    }

    public function testGetAllProducts()
    {
        $products = $this->products->ProductsShowable();
        $this->assertNotNull($products, "Products exist in category");
        $this->assertListEquals(
            array(
                array('URLSegment' => 'socks'),
                array('URLSegment' => 't-shirt'),
                array('URLSegment' => 'hdtv'),
                array('URLSegment' => 'beach-ball'),
            ),
            $products
        );
    }

    public function testSecondaryMembership()
    {
        $products = $this->electronics->ProductsShowable();
        $this->assertListEquals(
            array(
                array('URLSegment' => 'hdtv'),
            ),
            $products,
            'Should initially contain only direct membership products'
        );

        $this->socks->ProductCategories()->add($this->electronics);
        $this->socks->write();

        $products = $this->electronics->ProductsShowable();
        $this->assertListEquals(
            array(
                array('URLSegment' => 'hdtv'),
                array('URLSegment' => 'socks'),
            ),
            $products,
            'After adding a category via many-many to socks, that should show up as well'
        );
    }

    public function testZeroPrice()
    {
        Config::modify()->set(ProductCategory::class, 'must_have_price', true);

        $products = $this->products->ProductsShowable();
        $this->assertNotNull($products, "Products exist in category");
        // hdtv not in the list, since it doesn't have a base-price set
        $this->assertListEquals(
            array(
                array('URLSegment' => 'socks'),
                array('URLSegment' => 't-shirt'),
                array('URLSegment' => 'beach-ball'),
            ),
            $products
        );

        $this->socks->BasePrice = '';
        $this->socks->write();

        $products = $this->products->ProductsShowable();
        $this->assertListEquals(
            array(
                array('URLSegment' => 't-shirt'),
                array('URLSegment' => 'beach-ball'),
            ),
            $products
        );
    }

    public function testZeroPriceWithVariations()
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
            array(
                array('URLSegment' => 'hdtv')
            ),
            $products,
            'HDTV has a priced extension and should now show up in the list of products'
        );
    }

    public function testFiltering()
    {
        $this->markTestIncomplete('check filtering');
        //check published/ non published / allow purchase etc
        //Hide product if no price...or if product has variations, allow viewing.
    }
}
