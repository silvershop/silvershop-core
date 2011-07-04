# Customising the eCommerce module
It is most likely that you as a website developer/designer will want to customise the eCommerce module to look and work as your client would like.
Fortunately it is very easy to do so with the SilverStripe eCommerce module.

There are a number of configuration options to be set in your _config.php file. See [Configuration](Configuration)

## Theming / Templates
There are a number of templates you can customise to create your desired look for the eCommerce module.
The Order template has been modularised using SilverStripe's <% include TemplateName %> tag to provide varying degrees of freedom to customise. The order template is used in both the Account page to display summaries of past orders, and it is also used with the email template.
To make your customisations you need to create your own corresponding version of the template/partial-template with the same name in your mysite/templates folder or the themes folder.

More about developing themes [here](http://doc.silverstripe.org/sapphire/en/topics/theme-development).
Note: some templates are needed in multiple places to work.

## Sub modules
Sub-modules provide additional functionality that remains separate from the main eCommerce module to keep the core code as minimal as possible.

Here is a list of current sub-modules, along with their purpose:

* [Product Variations](https://silverstripe-ecommerce.googlecode.com/svn/modules/ecommerce_product_variation/trunk) 
* [Stock Control](https://silverstripe-ecommerce.googlecode.com/svn/modules/ecommerce_stockcontrol/branches/simple)
* [Discount Coupons](https://silverstripe-ecommerce.googlecode.com/svn/modules/ecommerce_coupon/trunk)
* [Brand browsing](http://silverstripe-ecommerce.googlecode.com/svn/modules/ecommerce_brandbrowsing/trunk/)

More unannounced modules can be found [here](https://code.google.com/p/silverstripe-ecommerce/source/browse/#svn%2Fmodules).


If you are interested in developing your own sub-module, see [contributing](Contributing) docs.

## Shipping & Tax Modifiers

SimpleShippingModifier
Allows you to choose different flat rates for different countries.

WeightShippingModifier
Requires entering a weight for each product.

FlatTaxModifier

TaxModifier


## Common Customisations

Here a number of common customisations to the ecommerce module.

### Order form fields

The OrderForm is what you at the checkout.

* Create an Extension of OrderForm eg: MyOrderFormExtension
* Create a function in your extension called either: updateValidator, updateFields, or updateForm

eg:

	function updateFields(&$fields){
		$fields->insertBefore(new TextField('State'),'Country');
	}

If Order does not contain 'State', you'll need to extend Order to add it to the db fields. The OrderForm data also gets saved to Member, if one exists, so you would need to add State to that also.

#### Custom validation

...

### Modify country dropdown field

...


Also see [Development](Development).