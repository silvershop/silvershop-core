<?php

namespace SilverShop\Core\Cart;

use PageController;

class CartPageController extends PageController
{
    private static $url_segment     = 'cart';

    private static $allowed_actions = array(
        "CartForm",
        "updatecart",
    );

    /**
     * Display a title if there is no model, or no title.
     */
    public function Title()
    {
        if ($this->Title) {
            return $this->Title;
        }
        return _t('CartPage.DefaultTitle', "Shopping Cart");
    }

    /**
     * A form for updating cart items
     */
    public function CartForm()
    {
        $cart = $this->Cart();
        if (!$cart) {
            return false;
        }
        $form = CartForm::create($this, "CartForm", $cart);

        $this->extend('updateCartForm', $form);

        return $form;
    }
}
