<?php
/**
 * Reset to all default configuration settings.
 */

// * * * NON-ECOMMERCE SETTINGS
Payment::set_site_currency('USD');
Geoip::$default_country_code = false;
//i18n::set_locale('');

// * * * ECOMMERCE I18N SETTINGS
EcommerceCurrency::setDecimalDelimiter('.');
EcommerceCurrency::setThousandDelimiter('');
Object::useCustomClass('SS_Datetime','I18nDatetime', true);

// * * * SHOPPING CART, ORDER, MODIFIERS
Order::set_email(null);
Order::set_receipt_subject("Shop Sale Information #%d");
Order::set_modifiers(array(),true);

Order::set_table_overview_fields(array(
		'ID' => 'Order No',
		'Created' => 'Created',
		'FirstName' => 'First Name',
		'Surname' => 'Surname',
		'Total' => 'Total',
		'Status' => 'Status'
	));
Order::set_maximum_ignorable_sales_payments_difference(0.01);
Order::set_order_id_start_number(0);

Order::set_cancel_before_payment(true);
Order::set_cancel_before_processing(false);
Order::set_cancel_before_sending(false);
Order::set_cancel_after_sending(false);

OrderForm::set_user_membership_optional(false);
OrderForm::set_force_membership(true);

OrderManipulation::set_allow_cancelling(false);
OrderManipulation::set_allow_paying(false);

// * * * PRODUCTS
ProductsAndGroupsModelAdmin::set_managed_models(array("Product", "ProductGroup","ProductVariation","ProductAttributeType"));
Product_Image::set_thumbnail_size(140, 100);
Product_Image::set_content_image_width(200);
Product_Image::set_large_image_width(200);
ProductGroup::set_include_child_groups(true);
ProductGroup::set_must_have_price(true);
ProductGroup::set_sort_options( array('Title' => 'Alphabetical','Price' => 'Lowest Price'));

// * * * CHECKOUT
ExpiryDateField::set_short_months(true);
OrderFormWithoutShippingAddress::set_fixed_country_code(null);
OrderFormWithoutShippingAddress::set_postal_code_url("http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder");
OrderFormWithoutShippingAddress::set_postal_code_label("find postcode");
OrderFormWithoutShippingAddress::set_login_invite_alternative_text('Please <a href="Security/login?BackURL=/">log in now</a> to retrieve your account details or create an account below.');

// * * * MEMBER
EcommerceRole::set_group_name("Shop Customers");

// * * * HELP
Product::set_global_allow_purchase(true);