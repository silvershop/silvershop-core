# Testing the eCommerce Module #
Testing is highly important for maintaining a quality product.

## Regression Testing

This [Google doc containing regression tests](https://spreadsheets.google.com/ccc?key=0AtHUrSaBxJY8dG8teWVNTFYzbThZYUhhLTNmT0FiUHc&hl=en) will give you an idea of the various things you can test manually.
In a nutshell, be sure to test the following main functionality:

 * Adding/removing items from cart
 * Placing an order

## Unit / Functional Testing

We insist that the eCommerce module contains a suite of unit tests that are updated with any changes to the code. Sub-Modules should also have their own test suites.
See the [sapphire testing documentation](http://doc.silverstripe.org/sapphire/en/topics/testing/index) for setup information etc.
See also: [http://www.phpunit.de/manual/3.5/en/index.html](http://www.phpunit.de/manual/3.5/en/index.html)

To run all ecommerce tests visit yoursite/dev/ecommerce and click the "Run all ecommerce unit tests" link.

**Note:** Be aware that your configuration in _config.php may affect your test results.

### Writing Tests

The products created by the ecommerce.yml file are all in draft form (unpublished). You need to publish products if you wish to test
functionality that involves adding products to the cart etc. 

### Tests that have/will be written

[Here](http://code.google.com/p/silverstripe-ecommerce/source/browse/?r=1000#svn%2Ftrunk%2Ftests) are some historic test classes that could be referenced when writing them.

 - ShoppingCart
  - add
  - remove / removeall
  - set quantity
  - clear cart
 - ShoppingCartController
  - test all links
 - EcommerceRole (Member)
 - Order
  - totals,subtotals,quantity
  - create / don't create membership
  - shipping/order addresses
 - Buyable (Decorator)
  - crud new type of buyable
  - cart operations with buyable
 - ProductGroup
  - pagination / filtering link tests
 - Product (Page)
  - can view
 - ProductBulkLoader
 - OrderStep
  - Follow steps
  - Edit steps
 - CheckoutPage
  - processing
  - country selection
 - AccountPage
 - Forms/processing?
 - Modifiers - tests the default modifiers.
 - Payment - should be in the payment class ideally
 - CartCleanupTaskTest
 
 - Javascript tests
 

More things to test:

 - Order shows up in cart, when logged in or not logged in.
 - Check things don't carry accross different sessions.
 - Place two orders, whilst logged out using the same email address.
 - Price checking
 - Call a bunch of basic links to view: product, product group, account. admin: products, sales ...along with a single product/sale
 - test all config options
 - test all tasks
