<?php

define('ECOMMERCE_DIR','ecommerce');

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');
// Extend Payment with e-commerce relationship. 
if(!class_exists('Payment')) user_error("You need to also install the Payment module to use the eCommerce module", E_USER_ERROR);
DataObject::add_extension('Payment', 'EcommercePayment');
//create controller for shopping cart
Director::addRules(50, array(
	ShoppingCart::$URLSegment . '/$Action/$ID/$OtherID' => 'ShoppingCart'
));

Object::add_extension("DevelopmentAdmin", "EcommerceDevelopmentAdminDecorator");
DevelopmentAdmin::$allowed_actions[] = 'ecommerce';