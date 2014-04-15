<?php
/**
 * Tests for product image. These could be easily merged into the main
 * Product tests if desired, but those tests are currently non-functional.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.14.2014
 * @package shop
 * @subpackage tests
 */
class ProductImageTest extends SapphireTest {

	protected static $fixture_file = 'shop/tests/fixtures/shop.yml';

	function setUp() {
		parent::setUp();
		$this->socks = $this->objFromFixture('Product', 'socks');
		$this->img1 = new Image;
		$this->img1->Filename = 'assets/ProductImageTest1.png';
		$this->img1->write();
		$this->img2 = new Image;
		$this->img2->Filename = 'assets/ProductImageTest2.png';
		$this->img2->write();
		$this->siteConfig = SiteConfig::current_site_config();
		$this->siteConfig->DefaultProductImageID = $this->img1->ID;
		$this->siteConfig->write();
		file_put_contents($this->img1->getFullPath(), 'dummy file');
		file_put_contents($this->img2->getFullPath(), 'dummy file');
	}

	function tearDown() {
		unlink($this->img1->getFullPath());
		unlink($this->img2->getFullPath());
	}

	function testProductWithImage() {
		$this->socks->ImageID = $this->img2->ID;
		$img = $this->socks->Image();
		$this->assertTrue($img && $img->exists(), 'should exist');
		$this->assertEquals($img->ID, $this->img2->ID, 'should not be the default');
	}

	function testProductWithMissingImage() {
		$this->img3 = new Image;
		$this->img3->Filename = 'assets/ProductImageTest3.png';
		$this->img3->write();
		$this->socks->ImageID = $this->img3->ID;
		$img = $this->socks->Image();
		$this->assertTrue($img && $img->exists(), 'should exist');
		$this->assertEquals($img->ID, $this->img1->ID, 'should be the default');
	}

	function testProductWithNoImage() {
		$img = $this->socks->Image();
		$this->assertTrue($img && $img->exists(), 'should exist');
		$this->assertEquals($img->ID, $this->img1->ID, 'should be the default');
	}

	function testProductWithNoDefaultImage() {
		$this->siteConfig->DefaultProductImageID = 0;
		$this->siteConfig->write();
		$img = $this->socks->Image();
		$this->assertFalse($img && $img->exists(), 'should not exist');
	}

}