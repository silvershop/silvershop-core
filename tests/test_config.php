<?php

Object::useCustomClass('SS_Datetime','I18nDatetime', true);

/// Reset to all default configuration settings.

$cfg = Config::inst();

//remove array configs (these get merged, rater than replaced)

$cfg->remove("Payment","allowed_gateways");
$cfg->remove("Order","modifiers");
$cfg->remove("ProductCatalogAdmin","managed_models");
$cfg->remove("ProductCategory","sort_options");

// non-ecommerce
$cfg->update('Member', 'unique_identifier_field', 'Email');
$cfg->update('Email', 'admin_email', 'test@myshop.com');
$cfg->update('Payment', 'allowed_gateways', array(
	'Dummy',
	'Manual'
));

// i18n
$cfg->update('ShopCurrency','decimal_delimiter','.');
$cfg->update('ShopCurrency','thousand_delimiter','');
$cfg->update('ShopCurrency','negative_value_format','-%s');

// products
$cfg->update('Product','global_allow_purchase',true);
$cfg->update('ProductCatalogAdmin','managed_models', array("Product", "ProductCategory","ProductVariation","ProductAttributeType"));
$cfg->update('Product_image','thumbnail_width',140);
$cfg->update('Product_image','thumbnail_height',100);
$cfg->update('Product_image','large_image_width',200);
$cfg->update('ProductCategory','include_child_groups',true);
$cfg->update('ProductCategory','page_length',10);
$cfg->update('ProductCategory','must_have_price',true);
$cfg->update('ProductCategory','sort_options',array('Title' => 'Alphabetical','Price' => 'Lowest Price'));

// cart, order
$cfg->update('Order','modifiers',array());
$cfg->update('Order','cancel_before_payment',true);
$cfg->update('Order','cancel_before_processing',false);
$cfg->update('Order','cancel_before_sending',false);
$cfg->update('Order','cancel_after_sending',false);
$cfg->update('ShoppingCart_Controller','direct_to_cart_page', false);

//modifiers
$cfg->update('FlatTaxModifier','name', 'NZD');
$cfg->update('FlatTaxModifier','rate', 0.15);
$cfg->update('FlatTaxModifier','exclusive', true);

$cfg->update('GlobalTaxModifier','country_rates', array(
	"NZ" => array("rate" => 0.15, "name" => "GST", "exclusive" => false)
));

$cfg->update('SimpleShippingModifier','default_charge', 10);
$cfg->update('SimpleShippingModifier','charges_for_countries', array('US' => 10,'NZ' => 5));

// checkout
$cfg->update('ShopConfig','email_from', null);
$cfg->update('ShopConfig','base_currency', 'NZD');
$cfg->update('SteppedCheckout','first_step', null);
$cfg->update('Address','requiredfields',array(
	'Address',
	'City',
	'State',
	'Country'
));
$cfg->update('OrderActionsForm', 'set_allow_cancelling', false);
$cfg->update('OrderActionsForm', 'set_allow_paying', false);


// injector resets
$classes = array(
	"OrderProcessor",
	"CheckoutComponentConfig",
	"Security",
	"PurchaseService"
);
foreach($classes as $class){
	$cfg->update('Injector',$class, array("class" => $class));
}
