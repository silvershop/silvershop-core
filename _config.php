<?php


// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

// Extend Payment with e-commerce relationship.
DataObject::add_extension('Payment', 'EcommercePayment');

Director::addRules(50, array(
	ShoppingCart::$URLSegment . '/$Action/$ID/$OtherID' => 'ShoppingCart'
));

SS_Report::register("SideReport", "EcommerceSideReport_AllProducts");
SS_Report::register("SideReport", "EcommerceSideReport_FeaturedProducts");
SS_Report::register("SideReport", "EcommerceSideReport_NoImageProducts");