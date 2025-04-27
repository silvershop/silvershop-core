<?php

namespace SilverShop\Tests\Extension;

use SilverShop\Page\Product;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\InterventionBackend;
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

    protected Product $socks;
    protected Image $img1;
    protected Image $img2;
    protected Image $img3;
    protected SiteConfig $siteConfig;

    public function setUp(): void
    {
        parent::setUp();

        // Copy test images for each of the fixture references
        foreach (Image::get() as $dataList) {
            $sourcePath = __DIR__ . '/images/' . $dataList->Name;
            $dataList->setFromLocalFile($sourcePath, $dataList->Filename);
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
        $this->img3 = Image::create();

        $this->siteConfig = SiteConfig::current_site_config();
        $this->siteConfig->DefaultProductImageID = $this->img1->ID;
        $this->siteConfig->write();
    }

    function testProductWithImage(): void
    {
        $this->socks->ImageID = $this->img2->ID;
        $image = $this->socks->Image();
        $this->assertTrue($image && $image->exists(), 'should exist');
        $this->assertEquals($image->ID, $this->img2->ID, 'should not be the default');
    }

    function testProductWithMissingImage(): void
    {
        $this->img3->Filename = 'assets/ProductImageTest3.png';
        $this->img3->write();
        $this->socks->ImageID = $this->img3->ID;
        $image = $this->socks->Image();
        $this->assertTrue($image && $image->exists(), 'should exist');
        $this->assertEquals($image->ID, $this->img1->ID, 'should be the default');
    }

    function testProductWithNoImage(): void
    {
        $image = $this->socks->Image();
        $this->assertTrue($image && $image->exists(), 'should exist');
        $this->assertEquals($image->ID, $this->img1->ID, 'should be the default');
    }

    function testProductWithNoDefaultImage(): void
    {
        $this->siteConfig->DefaultProductImageID = 0;
        $this->siteConfig->write();
        $image = $this->socks->Image();
        $this->assertFalse($image && $image->exists(), 'should not exist');
    }
}
