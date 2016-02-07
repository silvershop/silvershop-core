<?php

/**
 * Base class for modifier forms.
 * Provides a redirect back to the checkout page.
 *
 * @see        OrderModifier
 *
 * @package    shop
 * @subpackage forms
 */
class OrderModifierForm extends Form
{
    /*
    protected $order;

    public function __construct(Order $order, CheckoutPage $checkoutPage, $name, FieldList $fields, FieldList $actions, $validator = null) {
        $this->order = $order;

        parent::__construct($checkoutPage, $name, $fields, $actions, $validator);
    }
    */

    public function redirect($status = "success", $message = "")
    {

        if (Director::is_ajax()) {
            return $status; //TODO: allow for custom return types, eg json - similar to ShoppingCart::return_data()
        }
        Controller::curr()->redirect(CheckoutPage::find_link());
    }
}
