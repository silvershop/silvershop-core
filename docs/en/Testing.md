Testing the eCommerce Module
============================
Testing is highly important for maintaining a quality product.

Regression Testing
------------------
[Google doc containing regression tests](https://spreadsheets.google.com/ccc?key=0AtHUrSaBxJY8dG8teWVNTFYzbThZYUhhLTNmT0FiUHc&hl=en)



Unit Testing
------------
We insist that the eCommerce module contains a suite of unit tests that are updated with any changes to the code. Sub-Modules should also have their own test suites.
See the [sapphire testing documentation](http://doc.silverstripe.org/sapphire/en/topics/testing/index) for setup information etc.

TBA: At some stage an automated testing system will continually test the code, especially when new commits are made.


Tests:

 - ShoppingCart
  - add
  - remove / removeall
  - set quantity
  - clear cart
 - ShoppingCart_Controller
  - test all links
 - EcommerceRole (Member)
 - Order
  - Totals,SubTotals
  - Quantity
 - Product (Page)
  - can view
 - ProductGroup
 - ProductBulkLoader
 - OrderStep
 - CheckoutPage
  - Processing
 - AccountPage
 - Modifiers - tests the default modifiers.
 - Payment - should be in the payment class ideally
 
 [Here](http://code.google.com/p/silverstripe-ecommerce/source/browse/?r=1000#svn%2Ftrunk%2Ftests) are some historic test classes that could be referenced when creating these.