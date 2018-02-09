<?php

namespace SilverShop\Extension;

use SilverShop\Cart\ShoppingCart;
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
 */
class ViewableCartExtension extends Extension
{
    /**
     * Get the cart, and do last minute calculation if necessary.
     */
    public function Cart()
    {
        $order = ShoppingCart::curr();
        if (!$order || !$order->Items() || !$order->Items()->exists()) {
            return false;
        }

        return $order;
    }

    public function getContinueLink()
    {
        if ($cartPage = CartPage::get()->first()) {
            if ($cartPage->ContinuePageID) {
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

    public function getCartLink()
    {
        return CartPage::find_link();
    }

    public function getCheckoutLink()
    {
        return CheckoutPage::find_link();
    }
}
