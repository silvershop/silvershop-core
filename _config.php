<?php

define('SHOP_DIR','shop');

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'ShopMember');
// Extend Payment with e-commerce relationship.
if(!class_exists('Payment')) user_error("You need to also install the Payment module to use the shop module", E_USER_ERROR);
DataObject::add_extension('Payment', 'ShopPayment');
//create controller for shopping cart
Director::addRules(50, array(
	ShoppingCart_Controller::$url_segment . '/$Action/$Buyable/$ID' => 'ShoppingCart_Controller',
	'dev/shop' => 'ShopDevelopmentAdmin'
));

Object::add_extension("Page_Controller","ViewableCart");
Object::add_extension("ShoppingCart_Controller","ViewableCart");
Object::add_extension("OrderAttribute","OrderAttributeAJAX");
Object::add_extension("Order","OrderAJAX");
Object::add_extension("ComponentSet","OrderItemList");
Object::add_extension("SiteConfig", "ShopConfig");

//custom classes
Object::useCustomClass('Currency','ShopCurrency', true);
Object::useCustomClass('Versioned','FixVersioned');

//variations
DataObject::add_extension("Product","ProductVariationDecorator");
Object::add_extension("Product_Controller","ProductControllerVariationExtension");

//reports
SS_Report::register("SideReport", "ShopSideReport_AllProducts");
SS_Report::register("SideReport", "ShopSideReport_FeaturedProducts");
SS_Report::register("SideReport", "ShopSideReport_NoImageProducts");
SS_Report::register("SideReport", "ShopSideReport_HeavyProducts");