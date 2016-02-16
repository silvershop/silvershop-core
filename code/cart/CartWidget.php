<?php
/**
 * CartWidget displays the current contents of the user's cart.
 *
 * @package    shop
 * @subpackage widgets
 */
if (class_exists("Widget")) {

    class CartWidget extends Widget
    {
        private static $title       = "Shopping Cart";

        private static $cmsTitle    = "Shopping Cart";

        private static $description = "Displays the current contents of the user's cart.";

        function Cart()
        {
            return Controller::curr()->Cart();
        }
    }
}
