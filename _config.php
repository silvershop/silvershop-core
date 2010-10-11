<?php


// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

// Extend Payment with e-commerce relationship.
DataObject::add_extension('Payment', 'EcommercePayment');

Director::addRules(50, array(
	ShoppingCart_Controller::$URLSegment . '/$Action/$ID/$OtherID' => 'ShoppingCart_Controller'
));




//copy the lines between the START AND END line to your /mysite/_config file and choose the right settings
//===================---------------- START ecommerce MODULE ----------------===================
//cms
//StoreAdmin::set_managed_models(array('Order','Payment','OrderStatusLog', 'OrderItem', 'OrderModifier'));
//StoreAdmin::add_managed_models("MyEcommerceModifier");
//StoreAdmin::remove_managed_models("OrderItem");
//ProductsAndGroupsModelAdmin::set_managed_models(array("Product", "ProductGroup"));
//ProductsAndGroupsModelAdmin::add_managed_models("MyEcommerceModifier");
//ProductsAndGroupsModelAdmin::remove_managed_models("OrderItem");
////forms
//ExpiryDateField::set_short_months(true) ;
//OrderFormWithoutShippingAddress::set_fixed_country_code("NZ");
//OrderFormWithoutShippingAddress::set_postal_code_url("http:://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder");
//OrderFormWithoutShippingAddress::set_postal_code_label("postal code") ;
//OrderFormWithoutShippingAddress::set_login_invite_alternative_text('Please <a href="Security/login?BackURL=/">log in now</a> to retrieve your account details or create an account below.') ;
////model
//EcommercePayment::set_order_status_fully_paid("Paid");
//EcommercePayment::set_payment_status_not_complete("Incomplete");
//EcommercePayment::set_payment_status_success("Success");
//EcommerceRole::set_group_name("Shop Customers");
//Order::set_non_shipping_db_fields(array("Status", "Printed")); //the DB fields in Order that do not relate to shipping...
//Order::set_maximum_ignorable_sales_payments_difference(0.01);
//Order::set_order_id_start_number(123456789);
//Order::set_table_overview_fields(array('ID' => 'Order No','Created' => 'Created','MemberSummary' => 'Customer','Total' => 'Order Total'));
//Order::set_email("sales@mysite.co.nz");
//Order::set_subject("Thank you for your order");
//Order::set_modifiers(array('TaxModifier'));
//Order::set_cancel_before_payment(true);
//Order::set_cancel_before_processing(false);
//Order::set_cancel_before_sending(false);
//Order::set_cancel_after_sending(false);
//ShoppingCart::set_country("NZ");
////modifiers
//SimpleShippingModifier::set_default_charge(12.34);
//SimpleShippingModifier::set_charges_for_countries(array("NZ" => 11));
//TaxModifier::set_for_country("NZ", 0.125, "Goods and Services Tax", "inclusive");
////products
//Product::set_global_allow_purcahse(false);
//Product::set_thumbnail_size($width = 100, $height = 100);
//Product::set_content_image_width($width = 300);
//Product::set_large_image_width($width = 100);
//ProductGroup::set_include_child_groups($include = true);
//ProductGroup::set_page_length($length = 10);
//ProductGroup::set_must_have_price($must = true);
//ProductGroup::set_sort_options(array('Title' => 'Alphabetical','Price' => 'Lowest Price','NumberSold' => 'Most Popular');
////search
//OrderFilters::set_how_many_days_around(10);
//===================---------------- END ecommerce  MODULE ----------------===================

