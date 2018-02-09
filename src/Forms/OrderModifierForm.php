<?php

namespace SilverShop\Forms;

use SilverShop\Page\CheckoutPage;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\Form;

/**
 * Base class for modifier forms.
 * Provides a redirect back to the checkout page.
 *
 * @see OrderModifier
 *
 * @package    shop
 * @subpackage forms
 */
class OrderModifierForm extends Form
{
    public function redirect($status = 'success', $message = '')
    {
        if (Director::is_ajax()) {
            return $status; //TODO: allow for custom return types, eg json - similar to ShoppingCart::return_data()
        }
        Controller::curr()->redirect(CheckoutPage::find_link());
    }
}
