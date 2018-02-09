<?php

namespace SilverShop\Tests\Extension;

use SilverShop\Page\Product;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\InterventionBackend;
use SilverStripe\Assets\Tests\Storage\AssetStoreTest\TestAssetStore;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Tests for product image. These could be easily merged into the main
 * Product tests if desired, but those tests are currently non-functional.
 */
class ProductImageExtensionTest extends SapphireTest
{
    protected static $fixture_file = [
        __DIR__ . '/../Fixtures/shop.yml',
        __DIR__ . '/../Fixtures/Images.yml',
    ];

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Image
     */
    protected $img;

    /**
     * @var Image
     */
    protected $img2;

    /**
     * @var SiteConfig
     */
    protected $siteConfig;

    public function setUp()
    {
        parent::setUp();

        // Set backend root to /images
        TestAssetStore::activate('images');

        // Copy test images for each of the fixture references
        foreach (Image::get() as $image) {
            $sourcePath = __DIR__ . '/images/' . $image->Name;
            $image->setFromLocalFile($sourcePath, $image->Filename);
        }

        // Set default config
        InterventionBackend::config()->set(
            'error_cache_ttl',
            [
                InterventionBackend::FAILED_INVALID => 0,
                InterventionBackend::FAILED_MISSING => '5,10',
                InterventionBackend::FAILED_UNKNOWN => 300,
            ]
        );

        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->img1 = Image::get()->filter('Name', 'ImageA.png')->first();
        $this->img2 = Image::get()->filter('Name', 'ImageB.png')->first();

        $this->siteConfig = SiteConfig::current_site_config();
        $this->siteConfig->DefaultProductImageID = $this->img1->ID;
        $this->siteConfig->write();
    }

    function testProductWithImage()
    {
        $this->socks->ImageID = $this->img2->ID;
        $img = $this->socks->Image();
        $this->assertTrue($img && $img->exists(), 'should exist');
        $this->assertEquals($img->ID, $this->img2->ID, 'should not be the default');
    }

    function testProductWithMissingImage()
    {
        $this->img3 = new Image;
        $this->img3->Filename = 'assets/ProductImageTest3.png';
        $this->img3->write();
        $this->socks->ImageID = $this->img3->ID;
        $img = $this->socks->Image();
        $this->assertTrue($img && $img->exists(), 'should exist');
        $this->assertEquals($img->ID, $this->img1->ID, 'should be the default');
    }

    function testProductWithNoImage()
    {
        $img = $this->socks->Image();
        $this->assertTrue($img && $img->exists(), 'should exist');
        $this->assertEquals($img->ID, $this->img1->ID, 'should be the default');
    }

    function testProductWithNoDefaultImage()
    {
        $this->siteConfig->DefaultProductImageID = 0;
        $this->siteConfig->write();
        $img = $this->socks->Image();
        $this->assertFalse($img && $img->exists(), 'should not exist');
    }
}
