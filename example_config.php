<?php
/**
 * Example Shop configuration file.
 *
 * This file is tested in the 'ShopTest' unit test.
 *
 * copy the lines below to your mysite/_config.php file and set as required.
 */

//NON-ECOMMERCE SETTINGS
Payment::set_site_currency('NZD');
Payment::set_supported_methods(array(
	'ChequePayment' => 'Cheque'
));
Geoip::$default_country_code = "NZ";
i18n::set_locale('en_NZ');
setlocale (LC_TIME, 'en_NZ@dollar', 'en_NZ.UTF-8', 'en_NZ', 'nz', 'nz');

//ECOMMERCE I18N SETTINGS
ShopCurrency::setDecimalDelimiter('.'); //for Money formating
ShopCurrency::setThousandDelimiter(','); //for Money formating
Object::useCustomClass('SS_Datetime','I18nDatetime', true);

//SHOPPING CART AND ORDER
Order::set_email("sales@myshop.com");
Order::set_receipt_subject("Thank you for your order at www.myshop.com - Order #%d");
Order::set_modifiers(array("FlatTaxModifier", "SimpleShippingModifier"));

Order::set_table_overview_fields(array('Total' => 'Total','Status' => 'Status'));//
Order::set_maximum_ignorable_sales_payments_difference(0.001);//sometimes there are small discrepancies in total (for various reasons)- here you can set the max allowed differences

Order::set_cancel_before_payment(false);
Order::set_cancel_before_processing(false);
Order::set_cancel_before_sending(false);
Order::set_cancel_after_sending(false);

OrderForm::set_user_membership_optional(); //optional for user to become a member
OrderForm::set_force_membership(); //all users must become members if true, or won't become members if false

OrderManipulation::set_allow_cancelling(); //shows a cancel button on the order page
OrderManipulation::set_allow_paying(); //shows a payment form

//PRODUCTS
ProductCatalogAdmin::set_managed_models(array("Product", "ProductCategory","ProductVariation"));
Product_Image::set_thumbnail_size(140, 100);
Product_Image::set_content_image_width(200);
Product_Image::set_large_image_width(200);
ProductCategory::set_include_child_groups(true);
ProductCategory::set_must_have_price(true);
ProductCategory::set_sort_options( array('Title' => 'Alphabetical','Price' => 'Lowest Price'));

//CHECKOUT
ExpiryDateField::set_short_months(true); //uses short months (e.g. Jan instead of january) for credit card expiry date.

//MEMBER
ShopMember::set_group_name("ShopMembers");

//MODIFIERS
FlatTaxModifier::set_tax("0.15", "GST", $exclusive = false);
SimpleShippingModifier::set_default_charge(10);
SimpleShippingModifier::set_charges_for_countries(array('US' => 10,'NZ' => 5));
GlobalTaxModifier::set_for_country($country = "NZ", $rate = 0.15, $name = "GST", $inclexcl = "inclusive");

//HELP
Product::set_global_allow_purchase(false); //stops the sale of all products