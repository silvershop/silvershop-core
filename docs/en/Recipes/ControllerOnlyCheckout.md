# Controller only Checkout

You can run your site without creating actual checkout and cart pages.

Add the following director rules to your _config.php file:

    Director::addRules(50, array(
        CheckoutPage_Controller::$url_segment . '/$Action/$ID/$OtherID' => 'CheckoutPage_Controller',
        CartPage_Controller::$url_segment . '/$Action/$ID/$OtherID' => 'CartPage_Controller'
    ));

    
This will make the cart viewable at:

    yoursite.com/cart
    
..and the checkout:

    yoursite.com/checkout