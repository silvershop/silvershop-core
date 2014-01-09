<?php
/**
 * Reset to all default configuration settings.
 *
 */
$cfg = Config::inst();

// * * * NON-ECOMMERCE SETTINGS
$cfg->update('Email', 'admin_email', 'test@myshop.com');
$cfg->update('Payment', 'allowed_gateways', array(
	'Dummy',
	'Manual'
));

//i18n::set_locale('');

// * * * ECOMMERCE I18N SETTINGS
ShopCurrency::setDecimalDelimiter('.');
ShopCurrency::setThousandDelimiter('');
Object::useCustomClass('SS_Datetime','I18nDatetime', true);

// * * * SHOPPING CART, ORDER, MODIFIERS
OrderProcessor::set_email_from(null);
OrderProcessor::set_receipt_subject("Shop Sale Information #%d");
Order::set_modifiers(array(),true); //empty modifiers

Order::set_table_overview_fields(array(
	'ID' => 'Order No',
	'Created' => 'Created',
	'FirstName' => 'First Name',
	'Surname' => 'Surname',
	'Total' => 'Total',
	'Status' => 'Status'
));

Order::set_cancel_before_payment(true);
Order::set_cancel_before_processing(false);
Order::set_cancel_before_sending(false);
Order::set_cancel_after_sending(false);

OrderManipulation::set_allow_cancelling(false);
OrderManipulation::set_allow_paying(false);

// * * * PRODUCTS
ProductCatalogAdmin::set_managed_models(array("Product", "ProductCategory","ProductVariation","ProductAttributeType"));
Product_Image::set_thumbnail_size(140, 100);
Product_Image::set_content_image_width(200);
Product_Image::set_large_image_width(200);
ProductCategory::set_include_child_groups(true);
ProductCategory::set_must_have_price(true);
ProductCategory::set_sort_options( array('Title' => 'Alphabetical','Price' => 'Lowest Price'));

// * * * CHECKOUT
SteppedCheckout::$first_step = null; //disable stepped checkout first step

// * * * HELP
Product::set_global_allow_purchase(true);