# Recipes

Here are some short tutorials explaining how to implement specific features.

This is a work in progress. Some of these recipes may turn into submodules, or core features.

## Country Specific Modifiers

## Release or Expire a product at a certian time


## Single-Step, Multi-Step, and REST/javascript based solutions

A store may need few or many steps to complete an order. This module provides flexability
to allow orders to be processed in various ways.

 * [Custom Products](CustomProducts) - sell anything you want.
 * [Customising Fields](CustomisingFields) - add/remove fields from customers, orders.
 * [Single Step Solution](SingleStepSolution) - all order and payment details captured in a single page.
 * [Multi Step Solution](MultiStepSolution) - details progressively captured.
 * [API](API) - API / javascript solution
 
## Adding products with a form, rather than links

Update the product template to use a form, rather than links.

Disable ShoppingCart_Controller

## Multi-lingual Translated Shop

Add the following to your _config.php file:

	Object::add_extension('SiteTree', 'Translatable');
	Object::add_extension('SiteConfig', 'Translatable');
	Object::add_extension('ProductVariation', 'Translatable');
	
Futher info:
http://www.balbuss.com/setting-up-a-multilingual-site/
