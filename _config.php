<?php

define('SHOP_DIR',basename(__DIR__));
define('SHOP_PATH',BASE_PATH.DIRECTORY_SEPARATOR.SHOP_DIR);

if(!class_exists('Payment'))
	user_error("You need to also install the Payment module to use the shop module", E_USER_ERROR);

//extensions
SiteConfig::add_extension("ShopConfig");
Payment::add_extension("ShopPayment");
Page_Controller::add_extension("ViewableCart");
ShoppingCart_Controller::add_extension("ViewableCart");
OrderAttribute::add_extension("OrderAttributeAJAX");
Order::add_extension("OrderAJAX");
Member::add_extension('ShopMember');
Image::add_extension("Product_Image");
//variations
Product::add_extension("ProductVariationDecorator");
Product_Controller::add_extension("ProductControllerVariationExtension");

//custom classes
Object::useCustomClass('Currency','ShopCurrency', true);