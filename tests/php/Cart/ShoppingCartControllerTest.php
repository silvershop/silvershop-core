<?php

declare(strict_types=1);

namespace SilverShop\Tests\Cart;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\CartPage;
use SilverShop\Page\CartPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\SecurityToken;

/**
 * Cart HTTP behaviour (covers {@see CartPageController}; class name retained for fixture path / autoload historic mapping).
 */
final class ShoppingCartControllerTest extends FunctionalTest
{
    public static $fixture_file = [
        '../Fixtures/shop.yml',
        '../Fixtures/variations.yml',
        '../Fixtures/pages/Cart.yml',
    ];

    public static $disable_theme = true;

    protected static $use_draft_site = false;

    protected $autoFollowRedirection = false;

    protected static $extra_dataobjects = [
        CustomProduct_OrderItem::class,
    ];

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Product
     */
    protected $noPurchaseProduct;

    /**
     * @var Product
     */
    protected $draftProduct;

    /**
     * @var Product
     */
    protected $noPriceProduct;

    /**
     * @var ShoppingCart
     */
    protected $cart;


    protected function setUp(): void
    {
        parent::setUp();

        ShopTestBootstrap::setConfiguration(); //reset config
        ShoppingCart::singleton()->clear();

        // Needed, so that products can be published
        $this->logInWithPermission('ADMIN');

        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');

        // products that can't be purchased
        $this->noPurchaseProduct = $this->objFromFixture(Product::class, 'beachball');
        $this->draftProduct = $this->objFromFixture(Product::class, 'tshirt');
        $this->noPriceProduct = $this->objFromFixture(Product::class, 'hdtv');

        // publish some products
        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
        $this->noPurchaseProduct->publishSingle();
        $this->noPriceProduct->publishSingle();

        $this->objFromFixture(CartPage::class, 'cart')->publishSingle();

        $this->cart = ShoppingCart::singleton();
    }

    public function testAddToCart(): void
    {
        $url = CartPageController::add_item_link($this->mp3player);
        $httpResponse = $this->get($url);

        $this->assertEquals(302, $httpResponse->getStatusCode(), "Adding the mp3 player should work");

        $secondMp3 = $this->get(CartPageController::add_item_link($this->mp3player));
        $this->assertEquals(302, $secondMp3->getStatusCode(), "Adding a second mp3 player should work");

        $socks = $this->get(CartPageController::add_item_link($this->socks));
        $this->assertEquals(302, $socks->getStatusCode(), "Adding socks should work");

        $noPurchaseProduct = $this->get(CartPageController::add_item_link($this->noPurchaseProduct));
        $this->assertEquals(400, $noPurchaseProduct->getStatusCode(), "Cannot purchase a product if disabled");

        $draftProduct = $this->get(CartPageController::add_item_link($this->draftProduct));
        $this->assertEquals(404, $draftProduct->getStatusCode(), "Cannot purchase a product that is draft");

        $noPriceProduct = $this->get(CartPageController::add_item_link($this->noPriceProduct));
        $this->assertEquals(400, $noPriceProduct->getStatusCode(), "Cannot purchase a product");

        $items = ShoppingCart::curr()->Items();
        $this->assertNotNull($items);

        $this->assertEquals($items->Count(), 2, 'There are 2 items in the cart');

        $mp3playerItem = $items
            ->innerJoin("SilverShop_Product_OrderItem", '"SilverShop_OrderItem"."ID" = "SilverShop_Product_OrderItem"."ID"')
            ->find('ProductID', (string) $this->mp3player->ID);

        $this->assertNotNull($mp3playerItem, "Mp3 player is in cart");

        $this->assertEquals(
            $mp3playerItem->ProductID,
            $this->mp3player->ID,
            'We have the correct Product ID in the cart.'
        );
        $this->assertEquals($mp3playerItem->Quantity, 2, 'We have 2 of this product in the cart.');

        $this->get(
            CartPageController::set_quantity_item_link($this->mp3player, ['quantity' => 5])
        );

        $items = ShoppingCart::curr()->Items();
        $mp3playeritem =
            $items->innerJoin("SilverShop_Product_OrderItem", '"SilverShop_OrderItem"."ID" = "SilverShop_Product_OrderItem"."ID"')->find(
                'ProductID',
                (string) $this->mp3player->ID
            );
        $this->assertEquals($mp3playeritem->Quantity, 5, 'We have 5 of this product in the cart.');

        $this->assertEquals(
            $this->noPurchaseProduct->canPurchase(),
            false,
            'non-purcahseable product is not purchaseable'
        );
        $this->assertArrayNotHasKey(
            $this->noPurchaseProduct->ID,
            $items->map('ProductID')->toArray(),
            'non-purcahable product is not in cart'
        );
        $this->assertEquals($this->draftProduct->canPurchase(), true, 'draft products can be purchased');
        $this->assertArrayNotHasKey(
            $this->draftProduct->ID,
            $items->map('ProductID')->toArray(),
            'draft product is not in cart'
        );
        $this->assertEquals($this->noPriceProduct->canPurchase(), false, 'product without price is not purchaseable');
        $this->assertArrayNotHasKey(
            $this->noPriceProduct->ID,
            $items->map('ProductID')->toArray(),
            'product without price is not in cart'
        );

        $this->cart->clear();
    }

    public function testRemoveFromCart(): void
    {
        $this->get(CartPageController::set_quantity_item_link($this->mp3player, ['quantity' => 5]));
        $this->assertTrue($this->cart->get($this->mp3player) !== false, "mp3player item now exists in cart");

        $this->get(CartPageController::add_item_link($this->socks));
        $this->assertTrue($this->cart->get($this->socks) !== false, "socks item now exists in cart");

        $this->get(CartPageController::remove_item_link($this->socks));
        $this->assertFalse((bool)$this->cart->get($this->socks));

        $this->get(CartPageController::remove_item_link($this->mp3player));

        $mp3playeritem = $this->cart->get($this->mp3player);
        $this->assertNotNull($mp3playeritem, "product still exists");
        $this->assertEquals($mp3playeritem->Quantity, 4, "only 4 of item left");

        $hasManyList = ShoppingCart::curr()->Items();
        $this->assertNotNull($hasManyList, "Cart is not empty");

        $this->cart->clear();
        $this->assertEquals(
            ShoppingCart::curr(),
            null,
            'Cart is clear'
        );
    }

    public function testSecurityToken(): void
    {
        $enabled = SecurityToken::is_enabled();

        SecurityToken::enable();

        $productId = $this->mp3player->ID;
        $link = CartPageController::add_item_link($this->mp3player);
        $this->assertMatchesRegularExpression(
            '{^/?cart/add/SilverShop-Page-Product/' . $productId . '\?SecurityID=[a-f0-9]+$}',
            $link
        );

        $response = $this->get($link);
        $this->assertEquals(302, $response->getStatusCode());

        Config::modify()->set(CartPageController::class, 'disable_security_token', true);

        $link = CartPageController::add_item_link($this->mp3player);
        $this->assertEquals('cart/add/SilverShop-Page-Product/' . $productId, ltrim($link, '/'));

        $response = $this->get($link);
        $this->assertEquals(302, $response->getStatusCode());

        SecurityToken::disable();

        Config::modify()->set(CartPageController::class, 'disable_security_token', false);
        $link = CartPageController::add_item_link($this->mp3player);
        $this->assertEquals('cart/add/SilverShop-Page-Product/' . $productId, ltrim($link, '/'));

        $response = $this->get($link);
        $this->assertEquals(302, $response->getStatusCode());

        SecurityToken::enable();
        $response = $this->get($link);
        $this->assertEquals(400, $response->getStatusCode());

        if (!$enabled) {
            SecurityToken::disable();
        }
    }

    public function testVariations(): void
    {
        /**
         * @var Product $dataObject
         */
        $dataObject = $this->objFromFixture(Product::class, 'ball');
        $dataObject->publishSingle();

        /**
         * @var Product $variation
         */
        $variation = $this->objFromFixture(Variation::class, 'redLarge');
        /**
         * @var Product $ball2
         */
        $ball2 = $this->objFromFixture(Variation::class, 'redSmall');

        $this->logInWithPermission('ADMIN');
        $variation->publishSingle();
        $ball2->publishSingle();

        $this->get(CartPageController::add_item_link($variation));
        $this->get(CartPageController::add_item_link($ball2));
        $hasManyList = ShoppingCart::curr()->Items();
        $this->assertNotNull($hasManyList);
        $this->assertEquals($hasManyList->Count(), 2, 'There are 2 items in the cart');

        $this->get(CartPageController::remove_all_item_link($variation));
        $this->assertEquals($hasManyList->Count(), 1, 'There is 1 item in the cart');
        $this->assertFalse((bool)$this->cart->get($variation), "first item not in cart");
        $this->assertNotNull($this->cart->get($ball2), "second item is in cart");
    }

    public function testCanCommentOnCartLine(): void
    {
        // CartPage must be published because this test runs with $use_draft_site = false
        $cartPage = $this->objFromFixture(CartPage::class, 'cart');
        $cartPage->publishSingle();

        $this->get(CartPageController::add_item_link($this->mp3player));

        $item = ShoppingCart::curr()->Items()->first();
        $this->assertNotNull($item);

        $body = $this->get('cart')->getBody();
        $this->assertStringContainsString('Items[' . $item->ID . '][Comment]', $body);

        $comment = 'Please gift wrap this item';
        $response = $this->post(
            'cart/CartForm',
            [
                'Items' => [
                    $item->ID => [
                        'Quantity' => 1,
                        'Comment' => $comment,
                    ],
                ],
                'action_updatecart' => 'Update Cart',
            ]
        );
        $this->assertEquals(302, $response->getStatusCode());

        $item = ShoppingCart::curr()->Items()->byID($item->ID);
        $this->assertEquals($comment, $item->Comment);
    }

    public function testAddProductViaUrlWithQuantityQuery(): void
    {
        ShoppingCart::singleton()->clear();

        $url = CartPageController::add_item_link($this->mp3player, ['quantity' => 4]);
        $response = $this->get($url);
        $this->assertEquals(302, $response->getStatusCode(), 'Add with quantity should redirect');

        $item = $this->cart->get($this->mp3player);
        $this->assertNotNull($item);
        $this->assertSame(4, (int) $item->Quantity);

        $this->cart->clear();
    }

    public function testAddVariationViaUrlWithQuantityQuery(): void
    {
        ShoppingCart::singleton()->clear();

        $product = $this->objFromFixture(Product::class, 'ball');
        $product->publishSingle();

        $variation = $this->objFromFixture(Variation::class, 'redLarge');
        $variation->publishSingle();

        $url = CartPageController::add_item_link($variation, ['quantity' => 3]);
        $response = $this->get($url);
        $this->assertEquals(302, $response->getStatusCode(), 'Add variation with quantity should redirect');

        $item = $this->cart->get($variation);
        $this->assertNotNull($item);
        $this->assertSame(3, (int) $item->Quantity);

        $this->cart->clear();
    }

    public function testAddVariationsBulkPost(): void
    {
        ShoppingCart::singleton()->clear();

        $product = $this->objFromFixture(Product::class, 'ball');
        $product->publishSingle();

        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
        $redSmall = $this->objFromFixture(Variation::class, 'redSmall');
        $redLarge->publishSingle();
        $redSmall->publishSingle();

        $url = 'cart/addvariations';
        $data = [
            'ProductID' => (string) $product->ID,
            'VariantQuantity' => [
                (string) $redLarge->ID => '2',
                (string) $redSmall->ID => '1',
            ],
        ];

        if (SecurityToken::is_enabled()) {
            $data[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        $response = $this->post($url, $data);
        $this->assertEquals(302, $response->getStatusCode(), 'Bulk add variations should redirect');

        $itemLarge = $this->cart->get($redLarge);
        $itemSmall = $this->cart->get($redSmall);
        $this->assertNotNull($itemLarge);
        $this->assertNotNull($itemSmall);
        $this->assertSame(2, (int) $itemLarge->Quantity);
        $this->assertSame(1, (int) $itemSmall->Quantity);

        $this->cart->clear();
    }

    public function testAddVariationsSkipsZeroQuantity(): void
    {
        ShoppingCart::singleton()->clear();

        $product = $this->objFromFixture(Product::class, 'ball');
        $product->publishSingle();

        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
        $redSmall = $this->objFromFixture(Variation::class, 'redSmall');
        $redLarge->publishSingle();
        $redSmall->publishSingle();

        $url = 'cart/addvariations';
        $data = [
            'ProductID' => (string) $product->ID,
            'VariantQuantity' => [
                (string) $redLarge->ID => '0',
                (string) $redSmall->ID => '3',
            ],
        ];

        if (SecurityToken::is_enabled()) {
            $data[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        $response = $this->post($url, $data);
        $this->assertEquals(302, $response->getStatusCode());

        $this->assertNull($this->cart->get($redLarge));
        $itemSmall = $this->cart->get($redSmall);
        $this->assertNotNull($itemSmall);
        $this->assertSame(3, (int) $itemSmall->Quantity);

        $this->cart->clear();
    }

    public function testAddVariationsRejectsForeignVariation(): void
    {
        ShoppingCart::singleton()->clear();

        $product = $this->objFromFixture(Product::class, 'ball');
        $product->publishSingle();

        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
        $redLarge->publishSingle();

        $url = 'cart/addvariations';
        $data = [
            'ProductID' => (string) $product->ID,
            'VariantQuantity' => [
                (string) $redLarge->ID => '1',
                '999999999' => '1',
            ],
        ];

        if (SecurityToken::is_enabled()) {
            $data[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        $response = $this->post($url, $data);
        $this->assertEquals(400, $response->getStatusCode());

        $this->cart->clear();
    }

    public function testAddVariationsMethodNotAllowedOnGet(): void
    {
        $response = $this->get('cart/addvariations');
        $this->assertEquals(405, $response->getStatusCode(), (string) $response->getBody());
    }

    public function testCheckAccessAddvariations(): void
    {
        $cartPage = $this->objFromFixture(CartPage::class, 'cart');
        $controller = CartPageController::create($cartPage);
        $this->assertTrue($controller->checkAccessAction('addvariations'));
    }

    public function testCheckAccessSwitchvariation(): void
    {
        $cartPage = $this->objFromFixture(CartPage::class, 'cart');
        $controller = CartPageController::create($cartPage);
        $this->assertTrue($controller->checkAccessAction('switchvariation'));
    }

    public function testAddJsonFormat(): void
    {
        ShoppingCart::singleton()->clear();

        $url = CartPageController::add_item_link($this->mp3player, ['quantity' => 2]) . '&format=json';
        $response = $this->get($url);
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($json);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('message', $json);

        $item = $this->cart->get($this->mp3player);
        $this->assertNotNull($item);
        $this->assertSame(2, (int) $item->Quantity);

        $this->cart->clear();
    }

    public function testSwitchVariationEndpoint(): void
    {
        ShoppingCart::singleton()->clear();

        $product = $this->objFromFixture(Product::class, 'ball');
        $product->publishSingle();

        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
        $redSmall = $this->objFromFixture(Variation::class, 'redSmall');
        $redLarge->publishSingle();
        $redSmall->publishSingle();

        $this->get(CartPageController::add_item_link($redLarge, ['quantity' => 4]));
        $item = $this->cart->get($redLarge);
        $this->assertNotNull($item);
        $this->assertInstanceOf(\SilverShop\Model\Variation\OrderItem::class, $item);

        $query = [
            'ItemID' => (string) $item->ID,
            'VariationID' => (string) $redSmall->ID,
        ];
        if (SecurityToken::is_enabled()) {
            $query[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        $url = 'cart/switchvariation?' . http_build_query($query);
        $response = $this->get($url);
        $this->assertEquals(302, $response->getStatusCode(), (string) $response->getBody());

        $this->assertNull($this->cart->get($redLarge));
        $newItem = $this->cart->get($redSmall);
        $this->assertNotNull($newItem);
        $this->assertSame((int) $item->ID, (int) $newItem->ID);
        $this->assertSame(4, (int) $newItem->Quantity);
        $this->assertSame((int) $redSmall->ID, (int) $newItem->ProductVariationID);

        $this->cart->clear();
    }

    public function testSwitchVariationRunsModificationHook(): void
    {
        Order::add_extension(ShoppingCartControllerTest_ModificationExtension::class);

        try {
            ShoppingCart::singleton()->clear();

            $product = $this->objFromFixture(Product::class, 'ball');
            $product->publishSingle();

            $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
            $redSmall = $this->objFromFixture(Variation::class, 'redSmall');
            $redLarge->publishSingle();
            $redSmall->publishSingle();

            $this->get(CartPageController::add_item_link($redLarge, ['quantity' => 8]));
            $item = $this->cart->get($redLarge);
            $this->assertNotNull($item);

            $this->assertTrue(
                ShoppingCart::singleton()->switchOrderItemVariation($item, $redSmall)
            );

            $updated = $this->cart->get($redSmall);
            $this->assertNotNull($updated);
            $this->assertSame(5, (int) $updated->Quantity);
        } finally {
            Order::remove_extension(ShoppingCartControllerTest_ModificationExtension::class);
            ShoppingCart::singleton()->clear();
        }
    }
}
