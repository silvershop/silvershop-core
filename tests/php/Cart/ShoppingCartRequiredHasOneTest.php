<?php

declare(strict_types=1);

namespace SilverShop\Tests\Cart;

use PHPUnit\Framework\Attributes\DataProvider;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

/**
 * has_one relations in an order item's required_fields must distinguish cart lines,
 * whether configured as "Selection" or "SelectionID".
 *
 * @see https://github.com/silvershop/silvershop-core/issues/331
 */
final class ShoppingCartRequiredHasOneTest extends SapphireTest
{
    protected static $fixture_file = '../Fixtures/shop.yml';

    protected static $extra_dataobjects = [
        ShoppingCartRequiredHasOneTest_OrderItem::class,
    ];

    public static $disable_theme = true;

    protected static $use_draft_site = false;

    protected Product $product;

    protected ShoppingCart $cart;

    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();
        Config::modify()->set(Product::class, 'order_item', ShoppingCartRequiredHasOneTest_OrderItem::class);

        ShoppingCart::singleton()->clear();
        $this->cart = ShoppingCart::singleton();
        $this->product = $this->objFromFixture(Product::class, 'mp3player');
        $this->product->publishSingle();
    }

    public static function requiredFieldsProvider(): array
    {
        return [
            'relation name' => [['Selection']],
            'relation ID field' => [['SelectionID']],
        ];
    }

    #[DataProvider('requiredFieldsProvider')]
    public function testHasOneRequiredFieldSeparatesLines(array $required): void
    {
        Config::modify()->set(ShoppingCartRequiredHasOneTest_OrderItem::class, 'required_fields', $required);

        $first = Member::create(['Email' => 'first-331@example.com']);
        $first->write();
        $second = Member::create(['Email' => 'second-331@example.com']);
        $second->write();

        $this->assertTrue((bool) $this->cart->add($this->product, 1, ['SelectionID' => $first->ID]));
        $this->assertTrue((bool) $this->cart->add($this->product, 1, ['SelectionID' => $second->ID]));

        $items = $this->cart->current()->Items();
        $this->assertCount(2, $items, 'different relation IDs create separate lines');

        $this->assertTrue((bool) $this->cart->add($this->product, 2, ['SelectionID' => $first->ID]));
        $this->assertCount(2, $this->cart->current()->Items(), 'same relation ID reuses the existing line');

        $firstItem = $this->cart->get($this->product, ['SelectionID' => $first->ID]);
        $this->assertNotNull($firstItem);
        $this->assertSame($first->ID, (int) $firstItem->SelectionID);
        $this->assertEquals(3, $firstItem->Quantity);
        $this->assertSame(['SelectionID' => $first->ID], $firstItem->uniquedata());

        $secondItem = $this->cart->get($this->product, ['Selection' => $second->ID]);
        $this->assertNotNull($secondItem, 'relation name is accepted as a data key');
        $this->assertSame($second->ID, (int) $secondItem->SelectionID);
        $this->assertEquals(1, $secondItem->Quantity);
    }
}
