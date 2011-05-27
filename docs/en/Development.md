Development
===========

This information should be useful when developing with ecommerce.


## Mission
What the module authors hope to accomplish for users, store admins, and fellow developers.

### For the user

### For the store admin

### For the developer
A well thought, clearly defined and documented API that can be used to easily extend the module.



Release Process
---------------

 - Branch that is being released must be stable & passing unit tests.
 - Release candidate to find bugs, and get feedback - feature requests from this should go into tickets.
 - Make sure change-log is up-to-date
 - After a week or so, create a tag (create a remider to do this)
 - Create downloadable version
 - Update demo site with latest release
 - Update silverstripe.org extensions page
 - Post notification on forums, google group


Development practices
---------------------

 - Write a new unit test for a newly found bug
 - Unit tests MUST pass before committing and merging
 - Maintain backwards compatibility
 - Full support of standard sapphire features
 - Graceful degradation of javascript
 - Make use of design patterns
 - Comment code thoroughly
 - Write and update documentation along with changes
 - Major changes need to be backed up with solid reasoning
 - Consult external sources, such as google groups when consensus can't be reached
 - Modular code: high cohesion, low coupling 


How to work together without treading on each other's toes
----------------------------------------------------------

 - Agree on what are good development practices.
 - Develop major features in isolation. Only introduce them along side the stable code when they are actually stable.


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