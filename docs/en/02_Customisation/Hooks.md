Doing a search within the shop module code for `->extend(` will reveal the various points where you can call extensions via decorators/extension classes.

Here is a (possibly incomplete) list of hooks you can use by extending various classes:

*TODO: provide context/use information for each hook*

Product

 - updateCMSFields - update the default CMS fields
 - canPurchase
 - updateItemFilter
 - updateDummyItem
 - updateLink
 - updateImage - override/manipulate the Product::Image relation
 - updateForm

Product_Controller

 - updateForm

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
 - updateName - modify the full name returned by Order::getName()

Address

 - updateToString - modify the full address string returned by Address::toString()
 - updateName - modify the full name returned by Address::getName()

OrderItem

 - updateTotal
 - updateLinkParameters
