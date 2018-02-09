<?php

namespace SilverShop\Tests\Model\Variation;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * Test product variation capabilities.
 *
 * @link       ProductVariation
 * @link       ProductVariationDecorator
 * @package    shop
 * @subpackage tests
 */
class VariationTest extends SapphireTest
{
    public static $fixture_file   = __DIR__ . '/../../Fixtures/variations.yml';
    public static $disable_theme  = true;
    protected static $use_draft_site = true;

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Variation
     */
    protected $redlarge;

    public function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        $this->ball = $this->objFromFixture(Product::class, "ball");
        $this->mp3player = $this->objFromFixture(Product::class, "mp3player");
        $this->redlarge = $this->objFromFixture(Variation::class, "redlarge");
    }

    public function testVariationOrderItem()
    {
        $cart = ShoppingCart::singleton();

        //config
        Config::modify()
            ->set(Variation::class, 'title_has_label', true)
            ->set(Variation::class, 'title_separator', ':')
            ->set(Variation::class, 'title_glue', ', ');

        $emptyitem = $this->redlarge->Item();
        $this->assertEquals(1, $emptyitem->Quantity, "Items always have a quantity of at least 1.");

        $cart->add($this->redlarge);
        $item = $cart->get($this->redlarge);
        $this->assertTrue((bool)$item, "item exists");
        $this->assertEquals(1, $item->Quantity);
        $this->assertEquals(22, $item->UnitPrice());
        $this->assertEquals("Size:Large, Color:Red", $item->SubTitle());
    }

    public function testGetVariation()
    {
        $colorred = $this->objFromFixture(AttributeValue::class, "color_red");
        $sizelarge = $this->objFromFixture(AttributeValue::class, "size_large");
        $attributes = array($colorred->ID, $sizelarge->ID);
        $variation = $this->ball->getVariationByAttributes($attributes);
        $this->assertInstanceOf(Variation::class, $variation, "Variation exists");
        $this->assertEquals(22, $variation->sellingPrice(), "Variation price is $22 (price of ball");

        $attributes = array($colorred->ID, 999);
        $variation = $this->ball->getVariationByAttributes($attributes);
        $this->assertNull($variation, "Variation does not exist");
    }

    public function testGenerateVariations()
    {
        $color = $this->objFromFixture(AttributeType::class, "color");
        $values = array('Black', 'Blue'); //Note: black doesn't exist in the yaml
        $this->mp3player->generateVariationsFromAttributes($color, $values);

        $capacity = $this->objFromFixture(AttributeType::class, "capacity");
        $values = array("120GB", "300GB"); //Note: 300GB doesn't exist in the yaml
        $this->mp3player->generateVariationsFromAttributes($capacity, $values);

        $variations = $this->mp3player->Variations();
        $this->assertEquals($variations->Count(), 4, "four variations created");

        $titles = $variations->map('ID', 'Title')->toArray();
        $this->assertContains('Color:Black, Capacity:120GB', $titles);
        $this->assertContains('Color:Black, Capacity:300GB', $titles);
        $this->assertContains('Color:Blue, Capacity:120GB', $titles);
        $this->assertContains('Color:Blue, Capacity:300GB', $titles);
    }

    public function testPriceRange()
    {
        $range = $this->ball->PriceRange();
        $this->assertTrue($range->HasRange);
        $this->assertEquals(20, $range->Min->getValue());
        $this->assertEquals(22, $range->Max->getValue());
        $this->assertEquals(21, $range->Average->getValue());
    }

    public function testVaraitionsBulkLoader()
    {
        $this->markTestIncomplete('try bulk loading some variations ... generate, and exact entries');
    }
}
