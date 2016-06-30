<?php

class ProductCategoryTest extends FunctionalTest
{
    public static $fixture_file  = 'silvershop/tests/fixtures/shop.yml';
    public static $disable_theme = true;

    /** @var ProductCategory */
    protected $products;

    /** @var ProductCategory */
    protected $clothing;

    /** @var ProductCategory */
    protected $electronics;

    /** @var Product */
    protected $socks;

    /** @var Product */
    protected $tshirt;

    /** @var Product */
    protected $hdtv;

    /** @var Product */
    protected $beachball;

    /** @var Product */
    protected $mp3player;

    public function setUp()
    {
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

    public function testCanViewProductCategoryPage()
    {
        $products = $this->objFromFixture('ProductCategory', 'products');
        $this->get(Director::makeRelative($products->Link()));
    }

    public function testGetAllProducts()
    {
        $products = $this->products->ProductsShowable();
        $this->assertNotNull($products, "Products exist in category");
        $this->assertDOSEquals(
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
        $this->assertDOSEquals(
            array(
                array('URLSegment' => 'hdtv'),
            ),
            $products,
            'Should initially contain only direct membership products'
        );

        $this->socks->ProductCategories()->add($this->electronics);
        $this->socks->write();

        $products = $this->electronics->ProductsShowable();
        $this->assertDOSEquals(
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
        Config::inst()->update('ProductCategory', 'must_have_price', true);

        $products = $this->products->ProductsShowable();
        $this->assertNotNull($products, "Products exist in category");
        // hdtv not in the list, since it doesn't have a base-price set
        $this->assertDOSEquals(
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
        $this->assertDOSEquals(
            array(
                array('URLSegment' => 't-shirt'),
                array('URLSegment' => 'beach-ball'),
            ),
            $products
        );
    }

    public function testZeroPriceWithVariations()
    {
        Config::inst()->update('ProductCategory', 'must_have_price', true);

        $products = $this->electronics->ProductsShowable();
        $this->assertEquals(0, $products->count(), 'No product should be returned as there\'s no price set');

        // Create a variation for HDTV
        ProductVariation::create(array(
            'InternalItemID' => '50-Inch',
            'Price'          => 1200,
            'ProductID'      => $this->hdtv->ID
        ))->write();

        $products = $this->electronics->ProductsShowable();

        $this->assertDOSEquals(
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
