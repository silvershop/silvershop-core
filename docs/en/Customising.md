# Customising the eCommerce module
It is most likely that you as a website developer/designer will want to customise the eCommerce module to look and work as your client would like.
Fortunately it is very easy to do so with the SilverStripe eCommerce module.

There are a number of configuration options to be set in your _config.php file. See [Configuration](Configuration)


## Theming / Templates
There are a number of templates you can customise to create your desired look for the eCommerce module.
The Order template has been modularised using SilverStripe's <% include TemplateName %> tag to provide varying degress of freedom to customise. The order template is used in both the Account page to display summaries of past orders, and it is also used with the email template.
To make your customisations you need to create your own corresponding version of the template/partial-template with the same name in your mysite/templates folder or the themes folder.

More about customising themes here. >>link to ss site
Note: some templates are needed in multiple places to work.

## Sub modules
Sub-modules provide additional functionality that remains separate from the main eCommerce module to keep the core code as minimal as possible.

Here is a list of current sub-modules, along with their purpose:
* Product Variations 
* Stock Control
* Discount Coupons
* Brand browsing

If you are interested in developing your own sub-module, see [Contributing](Contributing)

## Shipping & Tax Modifiers
