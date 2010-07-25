<?php


// Extend the Member with e-commerce related fields.
DataObject::add_extension('Member', 'EcommerceRole');

// Extend Payment with e-commerce relationship.
DataObject::add_extension('Payment', 'EcommercePayment');

Director::addRules(50, array(
	ShoppingCart_Controller::$URLSegment . '/$Action/$ID/$OtherID' => 'ShoppingCart_Controller'
));



//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ start ECOMMERCE module ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ___ ORDER
//Order::set_modifiers(array());;
//Order::set_email("websales@silverstripe.co.nz");
//Order::set_subject("Thank you for your order at silverstripe.co.nz");
//Order::set_table_overview_fields(array());
//Order::set_order_id_start_number(1000);
//Order::$db["Status"] = 'Enum("New,Unpaid,PaymentConfirmed,QueryForCustomer,PartsOnOrder,Processing,Sent,Complete,AdminCancelled,MemberCancelled","New")';

// ___ ORDERFORMS
//TO BE IMPROVED USING EXTEND METHOD
//OrderFormWithoutShippingAddress::add_extra_field("tabName", new TextField("ExampleName");
//OrderFormWithoutShippingAddress::set_fixed_country_code("NZ");
//OrderFormWithoutShippingAddress::set_postal_code_url("http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder");
//OrderFormWithoutShippingAddress::set_postal_code_label("find postcode");
/*
//adding /?BackURL= as part of the string will automatically add the right back URL!
OrderFormWithoutShippingAddress::set_login_invite_alternative_text('
	If you have ordered from us before you will need to <a href="Security/login/?BackURL=">log in</a>.
	To have your password reset link sent to you please go to the <a href="Security/lostpassword/?BackURL=">password recovery page</a>.
');
*/
//ExpiryDateField::set_short_months(true);

// ___ PAYMENT
//Payment::set_site_currency('NZD');

// ___ MODIFIERS
//SimpleShippingModifier::set_charges_for_countries(array());
//SimpleShippingModifier::set_default_charge(10);
//TaxModifier::set_for_country('NZ', 0.125, 'GST', 'exclusive');

// ___ PRODUCTS - TO BE MOVED TO SITE CONFIG
//Product::set_global_allow_purcahse(false);
//Product::set_thumbnail_size($width = 140, $height = 100);
//Product::set_content_image_width($width = 200);
//Product::set_large_image_width($width = 200);
//ProductGroup::set_page_length(12);
//ProductGroup::set_must_have_price(true);
//ProductGroup::set_sort_options(true);

// ___ TASKS
//HourlyEcommerceGroupUpdate::set_group_name("customers");
// DO NOT FORGET TO IMPLEMENT CRON JOB

// *** CMS REPORTS
//SS_Report::register('ReportAdmin','AllOrdersReport');
//SS_Report::register('ReportAdmin','CurrentOrdersReport');
//SS_Report::register('ReportAdmin','UnprintedOrderReport');

// ___ CMS MODELADMIN
//ProductsAndGroupsModelAdmin::set_managed_models(Array("Product", "ProductGroup"));
//StoreAdmin::set_managed_models(array('Order','Payment','OrderStatusLog', 'OrderItem', 'OrderModifier'));

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ end ECOMMERCE module ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * NOTA BENE :: NOTA BENE :: NOTA BENE :: NOTA BENE :: NOTA BENE ::
 * @important: in the order templates, change as follows:
 * FROM: <td id="$TableTotalID" class="price"><% if IsChargable %>$Amount.Nice<% else %>-$Amount.Nice<% end_if %></td>
 * TO: <td id="$TableTotalID" class="price">$TableValue</td>
 **/


