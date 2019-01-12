<?php

namespace SilverShop\Tests\Cart;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Cart\ShoppingCartController;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\SecurityToken;

/**
 * @link ShoppingCart_Controller
 *
 * Test manipulating via urls.
 */
class ShoppingCartControllerTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    public static $disable_theme = true;
    protected static $use_draft_site = false;
    protected $autoFollowRedirection = false;

    // This seems to be required, because we query the OrderItem table and thus this gets included…
    // TODO: Remove once we figure out how to circumvent that…
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


    public function setUp()
    {
        parent::setUp();

        ShopTest::setConfiguration(); //reset config
        ShoppingCart::singleton()->clear();

        // Needed, so that products can be published
        $this->logInWithPermission('ADMIN');

        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        //products that can't be purchased
        $this->noPurchaseProduct = $this->objFromFixture(Product::class, 'beachball');
        $this->draftProduct = $this->objFromFixture(Product::class, 'tshirt');
        $this->noPriceProduct = $this->objFromFixture(Product::class, 'hdtv');

        //publish some products
        $this->mp3player->publishSingle();
        $this->socks->publishSingle();
        $this->noPurchaseProduct->publishSingle();
        $this->noPriceProduct->publishSingle();

        $this->cart = ShoppingCart::singleton();
    }

    public function testAddToCart()
    {
        // add 2 of the same items via url
        $this->get(ShoppingCartController::add_item_link($this->mp3player)); //add item via url
        $this->get(ShoppingCartController::add_item_link($this->mp3player)); //add another
        $this->get(ShoppingCartController::add_item_link($this->socks)); //add a different product
        $this->get(ShoppingCartController::add_item_link($this->noPurchaseProduct));  //add a product that you can't add
        $this->get(ShoppingCartController::add_item_link($this->draftProduct));  //add a product that is draft
        $this->get(ShoppingCartController::add_item_link($this->noPriceProduct));  //add a product that has no price

        // See what's in the cart
        $items = ShoppingCart::curr()->Items();
        $this->assertNotNull($items);

        $this->assertEquals($items->Count(), 2, 'There are 2 items in the cart');
        //join needed to provide ProductID
        $mp3playeritem = $items
            ->innerJoin("SilverShop_Product_OrderItem", "\"SilverShop_OrderItem\".\"ID\" = \"SilverShop_Product_OrderItem\".\"ID\"")
            ->find('ProductID', $this->mp3player->ID);

        $this->assertNotNull($mp3playeritem, "Mp3 player is in cart");

        // We have the product that we asserted in our fixture file, with a quantity of 2 in the cart
        $this->assertEquals(
            $mp3playeritem->ProductID,
            $this->mp3player->ID,
            'We have the correct Product ID in the cart.'
        );
        $this->assertEquals($mp3playeritem->Quantity, 2, 'We have 2 of this product in the cart.');

        // set item quantiy
        $this->get(
            ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5))
        ); //add item via url
        $items = ShoppingCart::curr()->Items();
        $mp3playeritem =
            $items->innerJoin("SilverShop_Product_OrderItem", "\"SilverShop_OrderItem\".\"ID\" = \"SilverShop_Product_OrderItem\".\"ID\"")->find(
                'ProductID',
                $this->mp3player->ID
            ); //join needed to provide ProductID
        $this->assertEquals($mp3playeritem->Quantity, 5, 'We have 5 of this product in the cart.');

        // non purchasable product checks
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

    public function testRemoveFromCart()
    {

        // add items via url
        $this->get(ShoppingCartController::set_quantity_item_link($this->mp3player, array('quantity' => 5)));
        $this->assertTrue($this->cart->get($this->mp3player) !== false, "mp3player item now exists in cart");
        $this->get(ShoppingCartController::add_item_link($this->socks));
        $this->assertTrue($this->cart->get($this->socks) !== false, "socks item now exists in cart");

        // remove items via url
        $this->get(ShoppingCartController::remove_item_link($this->socks)); //remove one different = remove completely
        $this->assertFalse((bool)$this->cart->get($this->socks));

        $this->get(ShoppingCartController::remove_item_link($this->mp3player)); //remove one product = 4 left

        $mp3playeritem = $this->cart->get($this->mp3player);
        $this->assertNotNull($mp3playeritem, "product still exists");
        $this->assertEquals($mp3playeritem->Quantity, 4, "only 4 of item left");

        $items = ShoppingCart::curr()->Items();
        $this->assertNotNull($items, "Cart is not empty");

        $this->cart->clear(); //test clearing cart
        $this->assertEquals(
            ShoppingCart::curr(),
            null,
            'Cart is clear'
        ); //items is a databoject set, and will therefore be null when cart is empty.
    }

    public function testSecurityToken()
    {
        $enabled = SecurityToken::is_enabled();
        // enable security tokens
        SecurityToken::enable();

        $productId = $this->mp3player->ID;
        // link should contain the security-token
        $link = ShoppingCartController::add_item_link($this->mp3player);
        $this->assertRegExp('{^shoppingcart/add/SilverShop-Page-Product/' . $productId . '\?SecurityID=[a-f0-9]+$}', $link);

        // should redirect back to the shop
        $response = $this->get($link);
        $this->assertEquals($response->getStatusCode(), 302);

        // disable security token for cart-links
        Config::modify()->set(ShoppingCartController::class, 'disable_security_token', true);

        $link = ShoppingCartController::add_item_link($this->mp3player);
        $this->assertEquals('shoppingcart/add/SilverShop-Page-Product/' . $productId, $link);

        // should redirect back to the shop
        $response = $this->get($link);
        $this->assertEquals($response->getStatusCode(), 302);

        SecurityToken::disable();

        Config::modify()->set(ShoppingCartController::class, 'disable_security_token', false);
        $link = ShoppingCartController::add_item_link($this->mp3player);
        $this->assertEquals('shoppingcart/add/SilverShop-Page-Product/' . $productId, $link);

        // should redirect back to the shop
        $response = $this->get($link);
        $this->assertEquals($response->getStatusCode(), 302);

        SecurityToken::enable();
        // should now return a 400 status
        $response = $this->get($link);
        $this->assertEquals($response->getStatusCode(), 400);

        // restore previous setting
        if (!$enabled) {
            SecurityToken::disable();
        }
    }

    public function testVariations()
    {
        $this->loadFixture(__DIR__ . '/../Fixtures/variations.yml');
        /**
         * @var Product $ballRoot
         */
        $ballRoot = $this->objFromFixture(Product::class, 'ball');
        $ballRoot->publishSingle();
        /**
         * @var Product $ball1
         */
        $ball1 = $this->objFromFixture(Variation::class, 'redlarge');
        /**
         * @var Product $ball2
         */
        $ball2 = $this->objFromFixture(Variation::class, 'redsmall');

        $this->logInWithPermission('ADMIN');
        $ball1->publishSingle();
        $ball2->publishSingle();

        // Add the two variation items
        $this->get(ShoppingCartController::add_item_link($ball1));
        $this->get(ShoppingCartController::add_item_link($ball2));
        $items = ShoppingCart::curr()->Items();
        $this->assertNotNull($items);
        $this->assertEquals($items->Count(), 2, 'There are 2 items in the cart');

        // Remove one and see what happens
        $this->get(ShoppingCartController::remove_all_item_link($ball1));
        $this->assertEquals($items->Count(), 1, 'There is 1 item in the cart');
        $this->assertFalse((bool)$this->cart->get($ball1), "first item not in cart");
        $this->assertNotNull($this->cart->get($ball2), "second item is in cart");
    }
}
