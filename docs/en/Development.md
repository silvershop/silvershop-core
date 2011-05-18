Development
===========

This information should be useful when developing with ecommerce.

Encouraged Development practices
---------------------

 - High cohesion, low coupling
 - Make use of design patterns
 - Comment code thoroughly
 - Write documentation
 - Unit tests

Tools
-----
You can find some useful development tools at yoursite/dev/ecommerce

Decorator Hooks
---------------
Doing a search within the eCommerce code for "->extend(" will reveal the various points where you can call extensions via decorators/extension classes.

Here is a (possibly incomplete) list of hooks you can use by extending various classes:

*TODO: provide context/use information for each hook*

Product

 - updateImport - called from ProductBulkLoader

Product_OrderItem

 - updateUnitPrice
 - updateTableTitle
 - updateTableSubTitle
 - updateDebug

ProductBulkLoader

 - updateColumnMap - for defining additional fields/functions that will process the specific CSV file values

Buyable

 - updateItemFilter
 - updateDummyItem
 - updateLinkParameters

EcommerceRole

 - augmentEcommerceFields
 - augmentEcommerceRequiredFields


OrderForm

 - updateValidator - mainly used to update required fields
 - updateFields - update the form fields
 - updateForm - other form changes

ShopAccountForm

 - updateShopAccountForm

Order

 - onInit
 - onCalculate

OrderItem

 - updateTotal
 - updateLinkParameters