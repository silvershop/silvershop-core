It is most likely that you as a website developer/designer will want to customise the shop module to look and work as your client would like.

There are a number of configuration options to be set in your _config files. See [Configuration](02_Customisation/Configuration.md)

[Recipes](02_Customisation/01_Recipes) contains a number of instructions for implementing specific features.

See also: [Customisation/Submodules](02_Customisation/Submodules.md).

See [Development](02_Customisation/Development.md) to understand the mission of this module.

## Where to put your customisations?

It may not be clear where to put your customisations of SilverStripe, and the shop module.
Here are some tips:

 * Look for the same idea already implemented in a [shop submodule](02_Customisation/Submodules.md) or a [SilverStripe module](http://addons.silverstripe.org/).
 You can save time if you find the work has already been done.
 * Create Extensions and [DataExtensions](http://doc.silverstripe.org/framework/en/reference/dataextension).
 This is the cleanest way of creating customisations. It may introduce slight overhead processing time however.
 * You should [create themes](http://docs.silverstripe.org/en/developer_guides/templates/themes/) in the themes directory, and split out into module directories, eg:
 	* themes
 		* mytheme
 		* mytheme_shop
 		* mytheme_blog

 * Site-specific code should go into mysite/code.

## Overriding core code

Hopefully you don't need to override core SilverStripe, or shop module code, but if you need to, you have a few options:

 * Rewrite the file directly. Not recommended if you aren't using a version control system
 like git. Git allows creating branches of code, which you can use to store your customisations. You should aim to submit these changes back to the project as improvements.
 * Create Sub-classes. Extend the parent class, and overwrite whatever methods you need to. For example:
 MyCustomCategory might extend ProductCategory. Or MyCustomProduct might extend Product.
 You can sometimes replace the use of one class with another by adding the following to your _config.php
 file: Object::useCustomClass("OldObject","NewObject");
 
 If these are bugfixes, or additional features that the core code would benefit from, please feel
 free to [contribute back](02_Customisation/Contributing.md).

## Theming / Templates

There are a number of templates you can customise to create your desired look for the shop module.
The Order template has been modularised using SilverStripe's <% include TemplateName %> tag to provide
varying degrees of freedom to customise. The order template is used in both the Account page to display
summaries of past orders, and it is also used with the email template.
To make your customisations you need to create your own corresponding version of the
template/partial-template with the same name in your mysite/templates folder or the themes folder.

More about developing themes [here](http://docs.silverstripe.org/en/developer_guides/templates/themes/).
Note: some templates are needed in multiple places to work.

## Modifiers

Shipping and tax, etc see [Order Modifiers](03_How_It_Works/Order_Modifiers.md)