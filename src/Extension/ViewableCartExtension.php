<?php

namespace SilverShop\Extension;

use PageController;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Cart\ShoppingCartController;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\ProductCategory;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;

/**
 * ViewableData extension that provides access to the cart from anywhere.
 * Also handles last-minute recalculation, if required.
 * All order updates: quantities, modifiers etc should be done before
 * this function is called.
 *
 * @package shop
 * @extends Extension<((PageController & static) | (ShoppingCartController & static))>
 */
class ViewableCartExtension extends Extension
{
    /**
     * Get the cart, and do last minute calculation if necessary.
     */
    public function Cart(): false|Order
    {
        $order = ShoppingCart::curr();
        if (!$order instanceof Order || !$order->Items() || !$order->Items()->exists()) {
            return false;
        }

        return $order;
    }

    public function getContinueLink(): string
    {
        if ($cartPage = CartPage::get()->first()) {
            if ($cartPage->ContinuePageID && $cartPage->ContinuePage()->exists()) {
                return $cartPage->ContinuePage()->Link();
            }
        }

        $maincategory = ProductCategory::get()
            ->sort(
                [
                    'ParentID' => 'ASC',
                    'ID' => 'ASC',
                ]
            )->first();
        if ($maincategory) {
            return $maincategory->Link();
        }

        return Director::baseURL();
    }

    public function getCartLink(): string
    {
        return CartPage::find_link();
    }

    public function getCheckoutLink(): string
    {
        return CheckoutPage::find_link();
    }
}
