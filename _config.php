<?php

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

// copy the lines below to your mysite/_config.php file and set as required.
// __________________________________START ECOMMERCE MODULE CONFIG __________________________________
//The configuration below is not required, but allows you to customise your ecommerce application - check for the defalt value first.
// * * * DEFINITELY MUST SET
//Order::set_email("your name <sales@myshop.com>);
//Order::set_subject("thank you for your order at www.myshop.com");
//Order::set_modifiers(array("MyModifierOne", "MyModifierTwo");


// * * * HIGHLY RECOMMENDED SETTINGS NON-ECOMMERCE
//Payment::set_site_currency('NZD');
//Geoip::$default_country_code = "NZ";
//i18n::set_locale('en_NZ');
//setlocale (LC_TIME, 'en_NZ@dollar', 'en_NZ.UTF-8', 'en_NZ', 'nz', 'nz');

// * * * ECOMMERCE I18N SETTINGS
//EcommerceCurrency::setDecimalDelimiter(','); //for Money formating
//EcommerceCurrency::setThousandDelimiter('.'); //for Money formating
//Object::useCustomClass('SS_Datetime','I18nDatetime', true);

// * * * SHOPPING CART AND ORDER
//ShoppingCart::set_fixed_country_code("NZ"); //always use the same country code
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
//ProductGroup::set_must_have_price(true);
//ProductGroup::set_sort_options( array('Title' => 'Alphabetical','Price' => 'Lowest Price')); // will be depreciated soon in this form, WATCH THIS SPACE.


// * * * CHECKOUT
//ExpiryDateField::set_short_months(true); //uses short months (e.g. Jan instead of january) for credit card expiry date.
//OrderFormWithoutShippingAddress::set_fixed_country_code("NZ"); //country is fixed
//OrderFormWithoutShippingAddress::set_postal_code_url("http://maps.google.com"); //link that can be used to check postal code
//OrderFormWithoutShippingAddress::set_postal_code_label("click here to check your postal code"); //label for link that can be used to check postal code
//OrderFormWithoutShippingAddress::set_login_invite_alternative_text('<a href="http://www.mysite.com/Security/login/?BackURL=">If you are a member then please log in.</a>); //label for link that can be used to check postal code

// * * * MEMBER
//EcommerceRole::set_group_name("Customers");

// * * * MODIFIERS
//FlatTaxModifier::set_tax("0.15", "GST", $exclusive = false);
//SimpleShippingModifier::set_default_charge(10);
//SimpleShippingModifier::::set_charges_for_countries(array('US' => 10,'NZ' => 5));
//TaxModifier::::set_for_country($country = "NZ", $rate = 0.15, $name = "GST", $inclexcl = "inclusive"))

// * * * HELP
//Product::set_global_allow_purcahse(false); //stops the sale of all products
// ------------------------------------------------------END ECOMMERCE MODULE CONFIG ------------------------------------------------------
