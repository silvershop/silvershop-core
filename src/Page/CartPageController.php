<?php

namespace SilverShop\Page;

use PageController;
use SilverShop\Extension\ViewableCartExtension;
use SilverShop\Forms\CartForm;

/**
 * Class CartPageController
 *
 * @mixin ViewableCartExtension
 * @extends PageController<CartPage>
 */
class CartPageController extends PageController
{
    private static string $url_segment = 'cart';

    private static array $allowed_actions = [
        'CartForm',
        'updatecart',
    ];

    /**
     * Display a title if there is no model, or no title.
     */
    public function Title(): string
    {
        if ($this->getFailover()->Title) {
            return $this->getFailover()->Title;
        }
        return _t('SilverShop\Page\CartPage.DefaultTitle', 'Shopping Cart');
    }

    /**
     * A form for updating cart items
     */
    public function CartForm(): CartForm|bool
    {
        $cart = $this->Cart();
        if (!$cart) {
            return false;
        }
        $form = CartForm::create($this, 'CartForm', $cart);

        $this->extend('updateCartForm', $form);

        return $form;
    }
}
