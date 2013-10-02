<?php
/**
 * Reset to all default configuration settings.
 */

$config = Config::inst();

// * * * NON-ECOMMERCE SETTINGS
$config->remove('Payment', 'set_site_currency');
$config->update(
	'Payment',
	'set_site_currency',
	'USD'
);

$config->remove('Email', 'setAdminEmail');
$config->update(
	'Email',
	'setAdminEmail',
	'test@myshop.com'
);


//i18n::set_locale('');

// * * * ECOMMERCE I18N SETTINGS
$config->remove('ShopCurrency', 'setDecimalDelimiter');
$config->update(
	'ShopCurrency',
	'setDecimalDelimiter',
	'.'
);

$config->remove('ShopCurrency', 'setThousandDelimiter');
$config->update(
	'ShopCurrency',
	'setThousandDelimiter',
	''
);

Object::useCustomClass('SS_Datetime','I18nDatetime', true);

// * * * SHOPPING CART, ORDER, MODIFIERS
$config->remove('OrderProcessor', 'set_email_from');
$config->update(
	'OrderProcessor',
	'set_email_from',
	null
);

$config->remove('OrderProcessor', 'set_receipt_subject');
$config->update(
	'OrderProcessor',
	'set_receipt_subject',
	"Shop Sale Information #%d",
);

$config->remove('Order', 'set_modifiers');
$config->update(
	'Order',
	'set_modifiers',
	array(array(), true),
);

// Order::set_modifiers(array(),true); //empty modifiers

$config->remove('Order', 'set_table_overview_fields');
$config->update(
	'Order',
	'set_table_overview_fields',
	array(
		'ID' => 'Order No',
		'Created' => 'Created',
		'FirstName' => 'First Name',
		'Surname' => 'Surname',
		'Total' => 'Total',
		'Status' => 'Status'
	)
);

$config->remove('Order', 'set_cancel_before_payment');
$config->update(
	'Order',
	'set_cancel_before_payment',
	true
);

$config->remove('Order', 'set_cancel_before_processing');
$config->update(
	'Order',
	'set_cancel_before_processing',
	false
);

$config->remove('Order', 'set_cancel_before_sending');
$config->update(
	'Order',
	'set_cancel_before_sending',
	false
);

$config->remove('Order', 'set_cancel_after_sending');
$config->update(
	'Order',
	'set_cancel_after_sending',
	false
);

$config->remove('OrderManipulation', 'set_allow_cancelling');
$config->update(
	'OrderManipulation',
	'set_allow_cancelling',
	false
);

$config->remove('OrderManipulation', 'set_allow_paying');
$config->update(
	'OrderManipulation',
	'set_allow_paying',
	false
);

// * * * PRODUCTS
$config->remove('ProductCatalogAdmin', 'set_managed_models');
$config->update(
	'ProductCatalogAdmin',
	'set_managed_models',
	array("Product", "ProductCategory","ProductVariation","ProductAttributeType")
);


$config->remove('Product_Image', 'set_thumbnail_size');
$config->update(
	'Product_Image',
	'set_thumbnail_size',
	array(140,100)
);

$config->remove('Product_Image', 'set_content_image_width');
$config->update(
	'Product_Image',
	'set_content_image_width',
	200
);

$config->remove('Product_Image', 'set_large_image_width');
$config->update(
	'Product_Image',
	'set_large_image_width',
	200
);


$config->remove('ProductCategory', 'set_include_child_groups');
$config->update(
	'ProductCategory',
	'set_include_child_groups',
	true
);

$config->remove('ProductCategory', 'set_must_have_price');
$config->update(
	'ProductCategory',
	'set_must_have_price',
	true
);

$config->remove('ProductCategory', 'set_sort_options');
$config->update(
	'ProductCategory',
	'set_sort_options',
	array('Title' => 'Alphabetical','Price' => 'Lowest Price')
);

// * * * CHECKOUT
$config->remove('ExpiryDateField', 'set_short_months');
$config->update(
	'ExpiryDateField',
	'set_short_months',
	true
);

SteppedCheckout::$first_step = null; //disable stepped checkout first step

Address::$required_fields = array(
	'Address',
	'AddressLine2',
	'State',
	'Country',
	'City',
	'PostalCode'
);

Address::$show_form_hints = true; //show form field hints

$config->remove('ShopPayment', 'set_supported_methods');
$config->update(
	'ShopPayment',
	'set_supported_methods',
	array(
		'Cheque' => 'Cheque'
	)
);

// * * * HELP

$config->remove('Product', 'set_global_allow_purchase');
$config->update(
	'Product',
	'set_global_allow_purchase',
	true
);