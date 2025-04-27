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
    public static bool $disable_theme  = true;
    protected static bool $use_draft_site = true;

    protected Product $mp3player;
    protected Product $socks;
    protected Product $ball;
    protected Variation $redLarge;

    public function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        $this->ball = $this->objFromFixture(Product::class, "ball");
        $this->mp3player = $this->objFromFixture(Product::class, "mp3player");
        $this->redLarge = $this->objFromFixture(Variation::class, "redLarge");
    }

    public function testVariationOrderItem(): void
    {
        $shoppingCart = ShoppingCart::singleton();

        //config
        Config::modify()
            ->set(Variation::class, 'title_has_label', true)
            ->set(Variation::class, 'title_separator', ':')
            ->set(Variation::class, 'title_glue', ', ');

        $emptyitem = $this->redLarge->Item();
        $this->assertEquals(1, $emptyitem->Quantity, "Items always have a quantity of at least 1.");

        $shoppingCart->add($this->redLarge);
        $item = $shoppingCart->get($this->redLarge);
        $this->assertTrue((bool)$item, "item exists");
        $this->assertEquals(1, $item->Quantity);
        $this->assertEquals(22, $item->UnitPrice());
        $this->assertEquals("Size:Large, Color:Red", $item->SubTitle());
    }

    public function testGetVariation(): void
    {
        $attributeValue = $this->objFromFixture(AttributeValue::class, "color_red");
        $sizelarge = $this->objFromFixture(AttributeValue::class, "size_large");
        $attributes = [$attributeValue->ID, $sizelarge->ID];
        $variation = $this->ball->getVariationByAttributes($attributes);
        $this->assertInstanceOf(Variation::class, $variation, "Variation exists");
        $this->assertEquals(22, $variation->sellingPrice(), "Variation price is $22 (price of ball");

        $attributes = [$attributeValue->ID, 999];
        $variation = $this->ball->getVariationByAttributes($attributes);
        $this->assertNull($variation, "Variation does not exist");
    }

    public function testGenerateVariations(): void
    {
        $attributeType = $this->objFromFixture(AttributeType::class, "color");
        $values = ['Black', 'Blue']; //Note: black doesn't exist in the yaml
        $this->mp3player->generateVariationsFromAttributes($attributeType, $values);

        $capacity = $this->objFromFixture(AttributeType::class, "capacity");
        $values = ["120GB", "300GB"]; //Note: 300GB doesn't exist in the yaml
        $this->mp3player->generateVariationsFromAttributes($capacity, $values);

        $hasManyList = $this->mp3player->Variations();
        $this->assertEquals($hasManyList->Count(), 4, "four variations created");

        $titles = $hasManyList->map('ID', 'Title')->toArray();
        $this->assertStringContainsString('Color:Black, Capacity:120GB', $titles[5]);
        $this->assertStringContainsString('Color:Black, Capacity:300GB', $titles[6]);
        $this->assertStringContainsString('Color:Blue, Capacity:120GB', $titles[7]);
        $this->assertStringContainsString('Color:Blue, Capacity:300GB', $titles[8]);
    }

    public function testPriceRange(): void
    {
        $range = $this->ball->PriceRange();
        $this->assertTrue($range->HasRange);
        $this->assertEquals(20, $range->Min->getValue());
        $this->assertEquals(22, $range->Max->getValue());
        $this->assertEquals(21, $range->Average->getValue());
    }
}
