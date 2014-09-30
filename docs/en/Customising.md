# Customising the shop module

It is most likely that you as a website developer/designer will want to customise the
shop module to look and work as your client would like.

There are a number of configuration options to be set in your _config.php file. See [Configuration](Configuration)

Also see the list of [recipes](Recipes) and also see [Development](Development)

## Where to put your customisations?

It may not be clear where to put your customisations of SilverStripe, and the shop module.
Here are some tips:

 * Look for the same idea already implemented in a [SilverStripe module](http://www.silverstripe.org/modules/),
 or [shop submodule](https://github.com/burnbright/silverstripe-shop/wiki/Sub-Modules).
 You can save time if you find the work has already been done.
 * Create Extensions and [DataExtensions](http://doc.silverstripe.org/framework/en/reference/dataextension).
 This is the cleanest way of creating customisations. It may introduce slight overhead processing time however.
 * You should [create themes](http://doc.silverstripe.org/framework/en/topics/theme-development) in the themes directory, and split out into module directories, eg:
 	* themes
 		* mytheme
 		* mytheme_shop
 		* mytheme_blog

 * Site-specific code should go into mysite/code.

## Overriding core code

Hopefully you don't need to override core SilverStripe, or shop module code. 

but if you need to, you have a few options:

 * Rewrite the file directly. Not recommended if you aren't using a version control system
 like git. Git allows creating branches of code, which you can use to store your customisations.
 * Create Sub-classes. Extend the parent class, and overwrite whatever methods you need to. For example:
 MyCustomCategory might extend ProductCategory. Or MyCustomProduct might extend Product.
 You can sometimes replace the use of one class with another by adding the following to your _config.php
 file: Object::useCustomClass("OldObject","NewObject");
 
 If these are bugfixes, or additional features that the core code would benefit from, please feel
 free to [contribute back](Contributing).

## Theming / Templates

There are a number of templates you can customise to create your desired look for the shop module.
The Order template has been modularised using SilverStripe's <% include TemplateName %> tag to provide
varying degrees of freedom to customise. The order template is used in both the Account page to display
summaries of past orders, and it is also used with the email template.
To make your customisations you need to create your own corresponding version of the
template/partial-template with the same name in your mysite/templates folder or the themes folder.

More about developing themes [here](http://doc.silverstripe.org/framework/en/topics/theme-development).
Note: some templates are needed in multiple places to work.

## Sub modules

Sub-modules provide additional functionality that remains separate from the main shop module to
keep the core code as minimal as possible. If you are creating custom/different functionality for your
website, and it could be used on other sites, then you should put this code into a submodule.

[List of shop sub-modules](https://github.com/burnbright/silverstripe-shop/wiki/Sub-Modules)

If you are interested in contributing your own sub-module(s), see [contributing](Contributing) docs.

## Modifiers

Shipping and tax, etc see [Order Modifiers](OrderModifiers)

## Common Customisations

See [Recipes/Common](Recipes/Common)