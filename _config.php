<?php

// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');
// Extend Payment with e-commerce relationship.
DataObject::add_extension('Payment', 'EcommercePayment');
// Extend SiteConfig with Ecommerce Extensions
DataObject::add_extension('SiteConfig', 'SiteConfigEcommerceExtras');
//create controller for shopping cart
Director::addRules(50, array(
	ShoppingCart::$url_segment . '/$Action/$ID/$OtherID' => 'ShoppingCart'
));


// copy the lines below to your mysite/_config.php file and set as required.
// __________________________________START ECOMMERCE MODULE CONFIG __________________________________
//The configuration below is not required, but allows you to customise your ecommerce application - check for the defalt value first.
// * * * DEFINITELY MUST SET
//Order::set_receipt_email("your name <sales@myshop.com>);
//Order::set_receipt_subject("thank you for your order at www.myshop.com");
//Order::set_modifiers(array("MyModifierOne", "MyModifierTwo");


// * * * HIGHLY RECOMMENDED SETTINGS NON-ECOMMERCE
//Payment::set_site_currency('NZD');
//Geoip::$default_country_code = "NZ";
//i18n::set_locale('en_NZ');
//setlocale (LC_TIME, 'en_NZ@dollar', 'en_NZ.UTF-8', 'en_NZ', 'nz', 'nz');
//Object::add_extension("Product", "EcommerceItemDecorator");
//Object::add_extension("ProductVariation", "EcommerceItemDecorator");


// * * * ECOMMERCE I18N SETTINGS
//EcommerceCurrency::setDecimalDelimiter(','); //for Money formating
//EcommerceCurrency::setThousandDelimiter('.'); //for Money formating
//Object::useCustomClass('SS_Datetime','I18nDatetime', true);

// * * * SHOPPING CART AND ORDER
//Order::set_table_overview_fields(array('Total' => 'Total','Status' => 'Status'));//
//Order::set_maximum_ignorable_sales_payments_difference(0.001);//sometimes there are small discrepancies in total (for various reasons)- here you can set the max allowed differences
//Order::set_order_id_start_number(1234567);//sets a start number for order ID, so that they do not start at one.
//Order::set_cancel_before_payment(false); //soon to be depreciated
//Order::set_cancel_before_processing(false); //soon to be depreciated
//Order::set_cancel_before_sending(false); //soon to be depreciated
//Order::set_cancel_after_sending(false); //soon to be depreciated

// * * * PRODUCTS
//ProductsAndGroupsModelAdmin::set_managed_models(array(("Product", "ProductGroup","ProductVariation"));
//SS_Report::register("SideReport", "EcommerceSideReport_AllProducts");
//SS_Report::register("SideReport", "EcommerceSideReport_FeaturedProducts");
//SS_Report::register("SideReport", "EcommerceSideReport_NoImageProducts");
//Product_Image::set_thumbnail_size(140, 100);
//Product_Image::set_content_image_width(200);
//Product_Image::set_large_image_width(200);
//ProductGroup::set_include_child_groups(true);
//ProductGroup::only_show_products_that_can_purchase(true);
//ProductGroup::add_sort_option( $key = "price", $title = "Lowest Price", $sql = "Price ASC");
//ProductGroup::remove_sort_option( $key = "title");
//ProductGroup::set_sort_options_default( $key = "price");
//ProductGroup::set_only_show_products_that_can_purchase(true);

// * * * CHECKOUT
//ExpiryDateField::set_short_months(true); //uses short months (e.g. Jan instead of january) for credit card expiry date.

// * * * MEMBER
//EcommerceRole::set_group_name("Customers");
//EcommerceRole::set_fixed_country_code("NZ"); //country is fixed
//EcommerceRole::set_postal_code_url("http://maps.google.com"); //link that can be used to check postal code
//EcommerceRole::set_postal_code_label("click here to check your postal code"); //label for link that can be used to check postal code
//EcommerceRole::set_login_invite_alternative_text('<a href="http://www.mysite.com/Security/login/?BackURL=">If you are a member then please log in.</a>);

// * * * MODIFIERS
//FlatTaxModifier::set_tax("0.15", "GST", $exclusive = false);
//SimpleShippingModifier::set_default_charge(10);
//SimpleShippingModifier::::set_charges_for_countries(array('US' => 10,'NZ' => 5));
//TaxModifier::::set_for_country($country = "NZ", $rate = 0.15, $name = "GST", $inclexcl = "inclusive"))

// * * * HELP
//Product::set_global_allow_purcahse(false); //stops the sale of all products

// * * * SPECIAL CASES
//OrderItem::disable_quantity_js();
//ShoppingCartset_response_class("EcommerceResponse")
// ------------------------------------------------------END ECOMMERCE MODULE CONFIG ------------------------------------------------------


//===================---------------- START payment MODULE ----------------===================
//Payment::set_site_currency("NZD");
/*
Payment::set_supported_methods(array(
	'PayPalPayment' => 'Paypal Payment'
));
*/
//===================---------------- END payment MODULE ----------------===================

