<?php

/*
 * This file is needed to identify this as a SilverStripe module 
 */

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

// Extend Payment with e-commerce relationship.
DataObject::add_extension('Payment', 'EcommercePayment');

Director::addRules(50, array(
	ShoppingCart_Controller::$URLSegment . '/$Action/$ID/$OtherID' => 'ShoppingCart_Controller'
));

SS_Report::register('ReportAdmin','AllOrdersReport');
//SS_Report::register('ReportAdmin','CurrentOrdersReport');
//SS_Report::register('ReportAdmin','UnprintedOrderReport');


?>