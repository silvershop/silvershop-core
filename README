###############################################
Ecommerce Module
###############################################

Maintainer Contact
-----------------------------------------------
see https://code.google.com/p/silverstripe-ecommerce/

Requirements
-----------------------------------------------
Payment Module 0.3+
Sapphire 2.4+

Documentation
-----------------------------------------------
see https://code.google.com/p/silverstripe-ecommerce/



Installation Instructions
-----------------------------------------------
1. Find out how to add modules to SS and add module as per usual.

2. copy configurations from this module's _config.php file
into mysite/_config.php file and edit settings as required.
NB. the idea is not to edit this module so that you can
upgrade this module in one go without redoing the settings.
Instead customise your application using your mysite folder.


Make sure the module root folder is named 'ecommerce' to ensure requirements work properly.


//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ start ECOMMERCE module ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ___ ORDER
//Order::set_modifiers(array());;
//Order::set_email("websales@silverstripe.co.nz");
//Order::set_subject("Order #%d - Thank you for your order at silverstripe.co.nz");
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




Tips
-----------------------------------------------------------

when running dev/build/ add: ?updatepayment=1 to migrate
payment data from 2.3 to 2.4 style (currency db field to
money db field).

how to change order status options:

create class in your mysite folder like this

class OrderDecoratorCustom extends DataObjectDecorator {

	function extraStatics() {
		return array(
			'db' => array(
				'Status' => 'Enum("New,ConfirmingPayment,QueryForCustomer,ProductsOnOrder,Dispatching,CustomerReceiving,Completed,MemberCancelled,AdminCancelled","New")',
			)
		);
	}
}

and then add the following to your _config.php file:

DataObjectDecorator::add_extension('Order', 'OrderDecoratorCustom');



