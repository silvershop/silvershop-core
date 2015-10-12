# SilverStripe Shop Change Log

## 1.1.1

 * Tests pass against SilverStripe 3.2 and increased coverage slightly
 * Better use of Object::create and therefore the injector
 * Fixes a few issue with translatability
 * Added function to check if checkout step exists
 * Added some extension hooks
 
### Enhancements:

 * Include jQuery javascript requirement in AddressBookCheckoutComponent
 * Use `getRequiredFields` in the Address' validate method.
 * Added CheckoutStep_Summary component config extension hook.
 * Support DI in PaymentForm and CheckoutComponent
 * Add extension point for ProductsShowable
 * Change new Form to Form::create

### Bug Fixes:

 * Changed PriceRange value to be use sellingPrice not Price
 * Increased Scrutinizer code coverage timeout
 * Don't return a form if no actions are present in OrderManipulation
 * FIX: ensure required fields are correctly looked up in the config
 * Added translation to 2 missing strings

## 1.1.0

 * Units for physical measurements are customisable
 * Hooks for better ajax support (see markguinn/silverstripe-ajax and markguinn/silverstripe-shop-ajax for one implementation)
 * 2 new reports and 3 new dashboard panels
 * Order processing emails refactored into OrderEmailNotifier (with some deprecations for 2.0)
 * Fixed several issues with checkout
 * Code cleanup in several places, most notably CartForm
 * Bugs fixed

## 1.0.0

 * Upgraded to SilverStripe version 3.
 * Switched from Payment module to Omnipay module.
 * Single form and multi-step checkout system.
 * Documentation has been updated.
 * Many shop sub-modules have been developed and updated and improved in conjunction with this release.
 
### Enhancements:

 * Added analytics to suggested modules
 * Added a bunch of add-on modules to README
 * Added gitter integration with travis
 * shop period report was failing in postgres Added test to reveal issue with ShopPeriodReport in prostgres
 * Added ability to update ProductCategory_Controller's ListSorter
 * Introduced place_before_payment config on Order to allow configuring checkout to place order before making payment.
 * Populate OrdersAdmin search form with selected Statuses. Added some comments
 * Added classes to OrderAdmin_* templates to allow for better styling
 * Added CONTRIBUTING.md
 * Added recipe for setting default admin panel to orders
 * Added total outstanding to orderadmin subtotals. Other small template improvements
 * Added open graph type function to set products to type 'product'. Allow specifying a minimum size the open graph image must be
 * Added in open graph image function to Product. This will be picked up by the open graph module, if it is installed.
 * Added 'SelectedPaymentMethod' to CheckoutStep_PaymentMethod, for including in templates.
 * Merge pull request #257 from markguinn/patch-1
 * Added 1.0.x-dev branch alias to composer.json
 * Creating an order instance should use the Injector
 * Allow order to hook into validate data on checkout
 * Added 'payablecart' fixture to yaml Orders
 * Added subtitle to order items
 * Added comment to Order->getModifier
 * Added in extraDataObjects flag for test which newly has classes implementing TestOnly interface
 * Add  for use in email templates
 * Added hook for updateVariationAddToCartAjax and updateVariationAddToCart
 * Added hook for updateVariationAddToCartAjax and updateVariationAddToCart
 * added updateVariationAddToCartMessage hook
 * Added ability to define title separator for getTitle
 * Added another cart to shop.yml fixture Converted ShopTest to a helper class.
 * Added hack to allow overriding Address required_fields
 * Added set MemberID on order in OrderProcessor (if not already set). This helps to ensure member is set, when a member is logge
 * Merge pull request #242 from markguinn/feature-default-image
 * Added default product image
 * Added discount submodule suggestion to composer.json. Removed old composer silverstripe-payment config.
 * Added country drop down to Address CMSFields
 * Tidied OrderManipulation to use ss3 ORM. Improved OrderActionsForm a bit also. Added some testing.
 * use correct config variables in OrderActionsForm …remove redundant config fields from OrderManipulation. Added some tests
 * Added CheckoutPage_payment template for independently styling the on-site payment entry page.
 * Added suggested submodule: comparison
 * Added instructions for populating shop
 * Added default ID to product of ‘-1’ . This will hide products from the site tree when they are created in model admin. Useful 
 * Added forTemplate function to ShopCurrency.
 * Added CMS icons for orders, catalog, and zone sections
 * Updated statics in ProductAttributeValue Added ‘Product’ reverse belongs_many_many relationship to ProductAttributeType
 * Added some extra data to dummyproducts.yml
 * Created CartForm, allowing for adjusting cart items by submitting a form. The Cart template is still used, and is embedde
 * Allow choosing type of quantity field for order items.
 * Added missing config to CheckoutComponentTest
 * Added ‘dependson’ functionality to checkout components. This meant that I could allow creating new memberships.
 * checkout component base class
 * Added error message to redirect url get param for CheckoutStep_Summary
 * Added note about payment module status
 * Added some badges for various services
 * Added onPayment and onPaid extension points in OrderProcessor
 * Added InternalItemID to ProductVariation searchable fields
 * Added updateProductCMSFields hook for better control of product fields Added updateSellingPrice hook to ProductVariation Swapp
 * Added allowed actions to Product_Controller for compatibility with silverstripe 3.1
 * Added summary fields to Zone
 * Updated DeleteProductsTask to show delete count
 * create international zone task, useful for quickly creating an international zone that can be customised to exclude some 
 * Introduced helper class "SortControl", for managing data that can be used in sort drop downs, and produce appropriate SQL
 * Allow setting default string to som
 * Allow the shop base folder to be something other than 'shop'
 * Improved popularity calculation algorithm to factor in age of sales and age of product
 * Added total to 'othercart' in shop yml test fixture
 * Created task to recalculate product popularity (based on sales numbers)
 * Added Product report, Customer report. Fixed sorting in ShopSales Report
 * Added ability to set individual payment status' in order admin.
 * Added more useful testing data to shop.yml fixture file.
 * Created a ShopSalesReport for observing sales for specific periods
 * Added more data to shop.yml and Addresses.yml fixture files
 * Added minor note about contributing to read me.
 GrandTotal functions. Added docs to explain rounding.
 * Added cart page to shop fixture, and ProductVersion to order items.
 * Added option to PopulateShopTask to create an international Zone, with every AllowedCountry enabled. This is a quick way 
 * Added 'docreateaccount' to list of CheckoutStep_Membership allowed actions
 * Provided hooks for all CheckoutStep forms
 * Added SetLocationForm, which is useful for getting location data from the user.

### API Changes:

 * Merge pull request #263 from silverstripe-iterators/pulls/zero-order-fix
 * API Mark Order.Paid=<date> on $allow_zero_order_total
 *  Added category functions to Product for fetching categories, or category ids associated with a  product.
 * Merge pull request #252 from silverstripe-iterators/pulls/cart-task
 * API Fixed CartCleanupTask, define time in mins rather than relative
 * changed ShoppingCart add and setQuantity functions to return the new/existing item, rather than returning true.
 * Removed deprecated code from Order. Updated example_config.php and test_config.php accordingly
 * Converted OrderItemsList to subclass of HasManyList, instead of being an extension. Overrode getComponents function in Or
 * allow defining saveablefields on the AddProductForm, for security, and to allow setting fields like UnitPrice
 * Created ShopPeriodReport, which can be used to create reports that apply to a specific period, and results can be grouped
 * Added XML function to ShopCountry, for displaying as XML
 * renamed OrderItem->place to OrderItem->onPlacement, to be consistent with other events.
 * created alias ShopConfig::current() which is just SiteConfig::current_site_config()
 * Added onPayment function to orderItem. This is called whenever an order's payments are completed, and can be overloaded l
 * Removed Address City aliases: Suburb, County, District …they are not exactly synonymous, and could be added as separate f
 * Enforce rounding when setting order 'Total'. Tidied up Total and GrandTotal functions. Added docs to explain rounding.
 * Deprecated 'maximum ignorable sales payments difference' field, in favour of using rounding precision instead.

### Bug Fixes:

 * Merge pull request #300 from markguinn/patch-member-addresses-rc2
 * Merge pull request #302 from markguinn/patch-calc-bug
 * Fixes a variable name error in OrderTotalCalculator
 * Fixes a checkout bug: Given a single-page checkout and given the membership component comes after the address book components,
 * use buyable's createItem function instead of creating an "OrderItem" for shop quantity field.
 * Merge pull request #282 from markguinn/patch-shoppingcart-error
 * Fixes small bug in ShoppingCart, triggered if the session goes expires and then a user clicks a remove product link
 * Fixed ShopPaymentTest, and updated composer requirement to omnipay 1.1
 * Check if buyable exists before getting image from it (OrderItem->Image) Fixes #248
 * shop period report was failing in postgres Added test to reveal issue with ShopPeriodReport in prostgres
 * ShopPeriodReport SQLQueryList closure should be php 5.3 compatible.
 * omnipay's transactionId represents the order reference. The transactionReference should be reserved for data that gateway
 * missing member breaks parameterFields function in unit tests
 * start/end dates in ShopPeriodReports weren't being used in the correct format.
 * Fixed pagination, sorting etc by creating/requiring a special kind of 'SQLQueryLsit' Fixed product report link Removed Week gr
 * Merge pull request #278 from markguinn/patch-print-order-fix
 * Fixed bug when printing order in admin
 * removed OrderModifierLazyLoadFix extension call from OrderModifier
 * Removed hack that for core that has been fixed in
 * Travis will test master branch of cms/framework, but failure is allowed.
 * getSelectedPaymentMethod throws error if nice=true, and method is not in list
 * insert payments grid field after Content, instead of before Notes, because sometimes Notes field doesn't exist.
 * Print functionality broke edit saving. made print button have an icon.
 * Reintroduced print from CMS functionality fixes #45 fixes #167
 * make open graph image url absolute
 * Merge pull request #271 from webtorque7/master
 * Fix recursive ChildCategories on ProductCategory
 * Merge pull request #264 from halkyon/sessids_fix
 * Merge commit 'ed68ce113385bb6ab3faee85a90fbef5390a8550'
 * Removed AccountNavigation $LinkingMode template calls, because they don't work. fixes #235 thanks @nimeso!
 * Fixing case where add_session_order() isn't called on $0 orders
 * display full country name in address readonly field updated shopconfig->getSingleCountry to allow returning full country 
 * address country was not saving properly with previous `getSingleCountry` improvement
 * Country address field should not be required if it is the only field available (and is read-only).
 * If there is only one country allowed, then we need to ensure that country overrides ShopUserInfo location.
 * shipping address checkout step should update billing address, if "separate billing" is not selected.
 * Merge pull request #260 from markguinn/patch-payment-bug1
 * Removed bad null return from OrderTotalCalculator
 * Fixed bug in payment form
 * added missing getCategoryIDs and getCategories functions to ProductVariations. relates to 7eee4a11c33cf581524a61d44195f7a
 * Get proper message from correct object in payment form
 * Removed unnecessary 'setWhere' that was breaking ShopSalesReport
 * Merge pull request #254 from silverstripe-iterators/pulls/fix-cartcleanuptest
 * Fixed SQL case sensitivity in CartCleanupTaskTest
 * Select Order.Paid field when using having in ShopPeriodReport. Fixes #253
 * Updated ShopPeriodReport to hopefully finally be compatible with pgsql
 * don't throw an error when trying to recalculate an order that isn't cart.
 * made ShopPeriodReport cross-db compatible. Disabled reporting by week, as this is hard to support.
 * Merge pull request #251 from madmatt/pulls/ProductImageTest-fix
 * Create assets/ directory in ProductImageTest if it doesn't exist
 * cart wasn't recalculating when needed.
 * Fixed 'receipt_email' configuration
 * Fixed OrderStatusLog usage
 * Error message shows 'Email' now, and passed through  to processPaymentResponse
 * Fix staging site not allowing any products to be tested.
 * make lazy loading hack work with pgsql
 * Fix travis tests by installing phpunit via composer
 * a hack solution to get around existing lazy loading issue see: https://github.com/silverstripe/silverstripe-framework/iss
 * Fixed reference to login_joins_cart fixes #249 thanks @nimeso
 * allow changing payment currency fixes #239
 * Make sure billing address is set, even if it isn't entered. Fixes #247
 * Offsite payments were preventing order from being viewed. Fixed by 'archiving' shopping cart session id to order manipula
 * Fixes bug where product reports itself as in the cart when it's not
 * Make sure order receipt is only sent once order updating has finished (so that sent data is correct). Fixes: #238
 * order outstanding payments were not working relates to: #229
 * Use correct cancel url configuration name. don't allow cart orders to show up in AccountPage
 * updated CustomProductTest to work with fixed Buyable canPurchase api
 * Fixes for issues picked up by scrutinizer
 * scrutinizer config file
 * Don't allow Order->calculate function to be called if it's status is not 'Cart'. Clear current order from the cart when placin
 * don't allow manual payments on orderActionsForm.
 * updated payment functionality, according to omnipay changes
 * updated scrutiniser timing fixed README stats badges
 * OrderManipulation allorders typo
 * OrderManipulation orderfromid was return a boolean, rather than int fixes #224
 * fixed various issues that were failing unit tests
 * use correct config variables in OrderActionsForm …remove redundant config fields from OrderManipulation. Added some tests
 * reorganised the OrderActionsForm to work better Started a unit test to check the actions form.
 * If only one payment method fix
 * Removed ability to hide products from the tree.^P This can be left to a recipe for edge-case development. fixes #210
 * Display category nesting in ProductCategories field.
 * Fixed sort options on category page
 * Merge pull request #213 from nimeso/patch-3
 * Fixed Image function if variation deleted
 * updated FeaturedProduct references
 * statics config changes fixes #209
 * allow removing items via CartForm by entering 0 or less for quantity. VaraitionField was always causing a ‘change’, 
 * update order after offsite payment has been made
 * Fixed Cart template to hide additional column, when not needed.
 * removed debugging code
 * use proper function when getting selectedPayment type
 * test product variation subtitle wasn’t working.
 * Removed deprecated code
 * use record editor for product variations. Fixes #193
 * NEW: Allow choosing type of quantity field for order items.
 * complete orders through OrderProcessor, according to latest checkout changes
 * OrderManipulation extension incorrectly applied to AccountPage, instead of AccountPage_Controller
 * only validate membership data if membership data is required, or password is not empty
 * order processor test correctly logs out admin user when necessary
 * all unit tests now passing
 * fixed most unit tests to comply with latest changes
 * FIX for issue #200: shopping cart couldn’t retrieve product variations. relates to: #200, #208, #146, #179
 * canPay check was failing
 * Fixed issues when running tests with mysql. Tidied some tests. Moved a few things to use the new ORM.
 * instantiation chaining isn’t available until PHP5.4
 * travis shop install dir incorrect
 * Added missing extension
 * broken SteppedCheckoutPage template
 * updated old `control` tag with `with`
 * Merge pull request #188 from markguinn/patch-popularity-div-by-zero
 * Fix to popularity calculation returns null if product was bought today.
 * Merge pull request #183 from moveforward/134-keep-variations
 * fixes #134 - variations deleted if owner is deleted (not staged)
 * Merge pull request #182 from moveforward/181-checkout-link
 * fixes #181 - corrects link in template
 * Merge pull request #179 from moveforward/146-buyablefromrequest
 * fixes #146 only live products returned for add to cart
 * payments reference typo in Order.php
 * updated configuration settings references on OrderForm.php
 * CustomerGroup exists check was incorrect
 * restore reference to $item->MainID in DropdownShopQuantityField
 * composer payment require was incorrect
 * Fixed up composer requirement to use proper version of payment
 * Cleared out some deprecated and unused code fixes #147
 * check sellingPrice instead of Price when deciding if ProductVariation ->canPurchase
 * removed old reference to FixVersioned. Thanks @nimeso.
 * get CMS search fields working again. Modified scaffolded search context, and restricted orderAdmin listings to only those
 * updated old references to Product_Image
 * Removed Product_Image subclass, in favour of extending Image. Fixed the ShopMigrationTask
 * updated composer file to install to proper directory. Renamed packagist repo to include 'silverstripe-'
 * Merge pull request #154 from nimeso/ss3-fixes-1
 * Required fields for OrderForm were broken
 * Fixed styling of orders in CMS
 * only add live products to cart. fixes #146
 * temp fix for product bulk loader. Actual fix blocked by https://github.com/silverstripe/sapphire/pull/1781
 * updated Zone cmsFieldsTo use Grid field
 * Re-introduced ProductBulkLoader fixes #136
 * Merge pull request #142 from nimeso/orderitemlist_fix
 * quantiy function to use new method name
 * Fixed routes.yml for shoppingcart controller Changed all references Director::redirect… to Controller::curr()->redirect Fixed 
 * Fixed structure of template list
 * Prevent RestrictionRegionCountryDropdownField from being set to visitor country. NEW: Allow setting default string to som
 * unchecked order status checkboxes were including 'Cart' statuses. Forced only specific statuses, if none are checked. BUG
 * Run completePayment code in OrderProcessor regardless of whether a receipt has been sent.
 * ShopSalesReport - exclude orders with no "paid" date from the report
 * Fixed member not saving to order in SteppedCheckout. Updated unit tests.
 * Fixed SQL escaping issue in RegionRestriction code
 * Fixed tests that broke when shop.yml fixture was updated.
 * fixed incorrect request function
 * correctly calculate weights etc via OrderItemList
 * Fixed CheckoutTest usage of assertType, which is incompatible with newer versions of PHPUnit
 * use readonly field for country election when there is only one, or none.
 * Various fixes to get unit tests working for SS3. Removed some unused files and code. fixes #119
 * useless function in OrderActionsForm causing segmentation fault
 * Email subject order ID wasn't showing up, because Reference is a string, not a number. (sprintf %s instead of %$d)

## 0.8.5

### Enhancements:

 * added hook to product link function
 * Added AddProductForm to Product_Controller. Improved AddProductForm to better support the 'buyables' concept.
 * improved debugging display for shoppingcart/debug. Colouring and showing item details.
 * Added security token to shopping cart links, and request handling. This helps prevent CSRF attacks.
 * Create unique links for updating carts with customized order items. Introduced $Buyable url param for better custom product handling, and removed OtherID url param.
 * Introduced .htaccess file for added security
 * Also added check for valid payment type, and items can be purchased.
 * Introduced OrderItemList, an extension of ComponentSet that provides Quantity and Plural functions to allow displaying cart total quantity. Updated SideCart.ss template to make use of these new functions.
 * Introduced heavy products report for finding products that might have incorrect weights.
 * Added $Form to cart template. Refactored SideCart to display better.
 * Improved ShopMigrationTask to handle Product VaraitionAttributeTypes relationship name change. Removed payment migration code, as it should in the payment module.
 * Introduced optional SQL-based delete for cart cleanup task. Its less safe, but it's faster.
 * Allow a different template to be used when rendering orders in the CMS. Re-introduced order.css file to provide good default order styling.
 * Created an config option for choosing where to direct after cart manipulations.
 * Created PopulateShopTask to populate the database with some dummy categories, products, and variations.
 * Custom product testing and documentation.
 * Enforced the buyable interface within shopping cart. Removed references to product variation. Introduced buyable_relationship static variable on OrderItem as a way to recognizese associations for custom buyable objects.
 * Default template and css updates, additions and removals.
 * created AddProductForm for adding products via a form submission. This should help with preventing carts from being created for no reason. Relates to #7
 * Template improvements, including adding images and subtitle to order content.
 * Introduced filtering / parameter system for cart items. This allows adding customized products to the cart, and have quantities automatically update, rather than adding completely new order items. fixes #22
 * Removed custom debug statements. These were confusing, because they only provided a sub-set of the full debug information that developers are used to.
 * updated Order_Content_Editable template to use better styling approaches.
 * Complete rewrite of ShoppingCart. Split into two classes: ShoppingCart and ShoppingCart_Controller. The ShoppingCart class is a singleton and provides restricted access to an order for adding/removing items, and clearing the cart completely.
 * cleaned up OrderItems. Removed some remaining bits of session-based cart functionality/variables that were not needed.
 * used Object::useCustomClass to swap Currency with EcommerceCurrency, for template purposes. Renamed all occurrences of EcommerceCurrency back to plain old Currency. Introduced CanBeFreeCurrency class, which simply displays "FREE" when it's value is 0;
 * moved migration code from requireDefaultRecords to ShopMigrationTask
 * Further overhaul of modifiers system. Renamed $order->CalculateModifiers() to $order->calculate(), as it applies to totals also. Fixed all modifiers and tests to use new format.
 * Moved default record creation into PopulateShopTask.
 * Major changes to modifiers system. They now are calculated via Order->CalculateModifiers, rather than internally. This is because they rely on a continuous calculation from the items SubTotal..through each modifier, and eventually producing the total.

### API Changes:

 * Made ShoppingCart_Controller direct function static, to allow it to be called from outside classes.
 * Split Order - Attributes relationship into Order - Items and Order - Modifiers so that sets can be distinctly retrieved and updated with the built in ComponentSet functionality.
 * Renamed EcommercePayment to ShopPayment.
 * Added cart and checkout links to ViewableCart. Added find_link to CartPage, along with the ability to display a cart without a CartPage.
 * Decoupled order processing from Order and OrderForm. Decoupled email creation from Order. Created OrderProcessor to handle processing / fulfillment. fixes #23 fixes #3
 * Renamed ProductGroup to ProductCategory. This new name better suits the purpose the class serves.

### Bug Fixes:

 * moved development admin to using proper url rule, rather than the decorator approach. The bug was that the 'shop' action was allowed on any controller.
 * fixed invalid reference when getting OrderItem links
 * fixed null reference for terms page on order form
 * Terms and conditions are now checked properly.
 * fixed links and function references in ShopDatabaseAdmin relates to renaming in 7843309144a42eed230ddf1816f1a5601a36093f
 * typo in product variation code
 * fixed order printing bug.
 * got variations working again with the new shopping cart improvements.
 * MatchObjectFilter was including has_ones that it shouldn't.
 * cart contents showing on checkout page. MINOR: Removed sessionID from order. Past orders are now stored in a session array instead.
 * orders now calculate during migration task, if they don't have a Total
 * Temporary solution for fixing Versioned, to allow storing product versions against order items. Fixes issue #15
 * updated reference to deprecated ShoppingCart function. Fixes issue #20
 * introduced an interim fix for versioned issue. Added static variable to disable using versioned.
 * remove modifiers that aren't in Order::. Deprecatd CartValue now points to TableValue, instead of TableTitle
 * Fixed ability to remove modifiers.

## 0.8.3

 * Renamed module to 'shop', and changed all 'ecommerce' directory references to 'shop', in line with new name for the module.
 * Added in CalculatedTotal to OrderAttribute for the purpose of permanantly storing old values, and helping with order read speed.
 * Made VariaionForm the default way to add product variations.
 * Allowed checkout page to not require a page model.
 * updated documentation to include packages, and sub packages for phpdoc
 * Ability to add product attribute values from product edit page, rather than model admin.
 * Removed OrderFormWithoutShippingAddress, and OrderFormWithShippingAddress

## 0.8.2

 * Modified order form to allow orders to be placed without becoming a member in the process.
 * Introduced EcommerceRole::associate_to_current_order() for choosing to join order to member on login.
 * Added CartPage action and template "finished", which displays the order just placed.
 * Copied all ecommerce member fields to Order, so that orders can be placed without member.
 * Added docs folder, along with some developer and user documentation
 * Introduced mysite/dev/ecommerce to get quick access to ecommerce dev tools
 * Began updating test suite
 * Introduced the ability to pay and cancel incomplete orders
 * Introduced ECOMMERCE_DIR constant to allow ecommerce directory to be different. Note that some paths are still make use of 'ecommerce'.

## 0.7 - 0.8.1	

 * Variations working again
 * Re-structured default templates to be more hierartical and extensible. Removed redundant templates.
 * Implemented new reciept design
 * Updated CMS Order interface
 * Introduced FullBillingAddress and FullShippingAddress functions on Order to provide ways to get combined address fields.

 * Merged in DBCart, Burnbright, and SunnySideUp branches
 * Depricated AllowPurchase function on Product & ProductVariation in favour of canPurcahse. SilverStripe has can____ capabilites built in, and AllowPurchase was overriding the DB field.
 * Improved ProductBulkLoader to allow setting ProductGroup, and linking up an image of the product.
 * Merged ShoppingCart and ShoppingCart_Controller into one class
 * Introduced filters/paremeters system for more complex cart situations

0.6.1

 * Modified code to work with SS 2.4, and payment trunk as @ revision 103257. This mainly involved supporting the Money class.
 * Separated out unnecessary css styling. The default style is very much tied to black candy. (This can be put into a theme)
 * Improved efficiency of ProductGroup to make one database call to retrieve products. All ProductGroup children are retrieved by default, rather than just the immediate children.
 * Removed 'ShowInMenus' condition for displaying group products.
 * Added sorting controls to ProductGroup pages
 * Added pagination to default ProductGroup template
 * Removed separation of featured and non-featured products. The default sort is set to show featured products, then the rest by title (similar to TradeMe.co.nz)
 * Tidied up invoice printing
 * Got the 'all orders' SS report working again
 * Added 'Store' model admin for orders
 * Show products in multiple categories (does not yet include recursive sub-category products)
 * Variations of the same product can now be added to the cart together
 * Prevented order payment form showing on checkout template if nothing is in shopping cart.
 * Re-ordered CMS fields so they are more visible (eg price, weight, model)
 * Updated sitetree icons (product = package, checkout = shopping cart, account = contact card)
 * Include shopping cart page type
 * Removed quantity selectors from products on group page, as they can be updated using the cart on the left.
 * Added support for calculating and storing the number of products sold
	
0.6

 * Data model changes (see http://doc.silverstripe.com/doku.php?id=ecommerce:overview&s=ecommerce)
 * PHP files moved into folders for grouping of models, controllers and forms
 * Fixed undefined find_link() function on AccountPage_Controller
 * Check that the member can create a member with the unique field
 * Added translation for Arabic (Saudi Arabia) - thanks to Talal
 * Fixed template call to Text::LimitWordCountPlainText()
 * Fixed ID quoting in Product_Controller->addVariation()
 * #3939 Ability to show all products in ProductGroup
 * Fixed ShoppingCart index item to be the product ID
 * Re-added link methods back to Product_OrderItem from OrderItem
 * More agressive checking of Payment before creating a new Order on OrderForm
 * Added empty statics to various ecommerce classes to support decoration of statics via DataObjectDecorator
 * Creation of OrderItem with Product data record properly
 * Fixed Order::isPaid() to correspond to the Status enum field
 * If EcommerceRole::findCountry() cannot find the user's country, don't cause an error
 * Fixed Order->_ModifiersSubTotal() to exclude classes properly
 * Stopped errors occurring if calling shoppingcart/additem without an index ID
 * Fixed failing from address in the email for status updates
 * Fixed order status log not working properly
 * Changed reports to use TableListField, and fixed printing
 * Removed old CheckoutPage.js code that was broken, replaced with working version
 * Renamed MemberForm to ShopAccountForm since this is too general
 * Removed specifically set CMS fields, these are now scaffolded. 
 * Moved payment class URL rules to payment module _config.php
 * Product title not displayed in Receipt Email. Ticket #3680 
 * Fixed price still showing even if price set to 0 in product 
 * Moved Eway.js from ecommerce to payment module
 * Moved Eway payments to payments module
 * Removed restrictive decimal for Tax Rate field and replaced with double type
 * Gracefully degrade if member is not logged in on MemberForm
 * Fixed setRelationAutoSetting method that may not exist in old SS version
 * Add payment decorator to ecommerce, since payment classes now split into payments module
 * Added information about payment module being required
 * Separate payments into a separate module ("payment" module)
 * disable two deprecated functions in ecommerce/code/_config.php
 * Fixing usage of deprecated APIs
 * Make sure array in set_payment_methods() is associative
 * Cleaned up OrderReport to use non-deprecated APIs, refactored to use TableListField
 * Update i18n entities since the Report class was renamed
 * Updated ecommerce report classes to reflect change from Report to SSReport 
 * removed ecommerce jquery directory that isn't being used anymore
 * Removed javascript that shouldn't be done until we've got more of a stable platform
 * Instead of hardcoding css/js requirements into Report.php
 * Deleted ViewAllProducts.ss which was a relic of the now deleted DataReport API
 * Removed "abstract" Report class, which is now in the cms module
 * documentation of vital methods on Payment class
 * added links to examples for LiveAmount() on OrderModifier
 * Added documentation to OrderModifier::is_chargable
 * If amount for an OrderModifier is not chargable, then show a minus sign
 * added a better description to OrderModifier->TableTitle
 * Lots of code documentation and cleanup of code
 * Changed Order_Attribute to OrderAttribute, since this is operates on its own separate from Order
 * If a product can't be purchased, should still be able to see them but just can't add to cart
 * Paystation Hosted Payment added (now in "payment" module)
 * Fixed boundary condition in SimpleShippingModifier
 * jQuery code to replace existing prototype in ecommerce
 * Fixed requirement of $_SERVER[REMOTE_ADDR] for Payment class
 * Product version is now retained when product added to cart (so price changes don't affect orders)
 * Fixed bug with TaxModifier::AddedCharge()

0.5.1

 * Use the Session class rather than accessing $_SESSION directly
 * Template changes for 2.1.0
 * Use themes

0.5

 * Initial release
