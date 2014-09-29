# Development

This information should be useful when developing with the shop module.

## Mission
What the module authors hope to accomplish for users, store admins, and fellow developers.

### For the developer

A well thought, clearly defined and documented API that can be used to easily extend the module.

### For the store admin

Ability to easily manage the shop's products and orders. Useful reporting to understand and
improve sales.

### For the user

A fast, secure, easy to use interface. Provided options and flexability with the browsing and
ordering process.

## Release Process

See [release checklist](https://github.com/burnbright/silverstripe-shop/wiki/Release-Checklist)

## Tools

You can find some useful development tools at yoursite/dev/shop

## Batch Tasks

There are a number of manual and automated tasks that can be set up and run. The manual tasks can be 
accessed from yoursite/dev/shop.

If you have a large number of dataobjects, it may pay to run these tasks from the command line, for example:

    [rootdir]: sapphire/sake dev/tasks/CartCleanupTask

### CartCleanupTask

This will remove old carts from the database to help keep the number of carts down. You can specify the age of carts
in days to clear from (default is 90 days old). 

### CustomersToGroupTask

Adds members who have placed orders to the selected customer group (see the shop config). Useful for maintaining a
distinction between shop customers and other members.

## Decorator Hooks

Doing a search within the shop module code for "->extend(" will reveal the various points where you can call extensions
via decorators/extension classes.

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

ShopMember

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