# Order Modifiers

A cart holds two basic items: 

 * __Items__ - the actual product lines in an order, which usually contain a quantity and unit price.
 * __Modifiers__ - additional lines that specify costs or deductions on the items subtotal.

They provide an abstract way to add additional (non-product) lines to your order.

Example modifiers:

 * Tax Charge - such as GST, VAT
 * Delivery Charges - postage provider
 * Automatic Discounts - eg save 50% in March
 * Coupon Discounts - requiring the use of a code
 * Credit Note Usage - using funds available on account
 * Payment Gateway - passing on the cost
 
Things you should know about modifiers:

 * Modifiers can be updated at any point before an order is placed. After that, their values are permanently persisted to the database.
 * Modifier values are recalculated whenever the cart contents are changed, or 

## How to add a modifier type

Use Order::set_modifiers(array("MyModifierOne", "MyModifierTwo")); in your _config file to register new modifiers.

## Developing New Modifiers

The best way to write a modifier is to copy the modifier class itself and 

  * delete any methods that should stay the same
  * keep + adjust any methods that are different
  * add any other (protected / private) methods and (static) variables that you may need to use. 

There are lots of comments in the code that will help you.  You may also want to review two other classes:

  * OrderAttribute (you may want to overload some of the methods)
  * Other OrderModifier sub-classes
  
## Removing Modifier Types

Removing modifiers from the modifiers array should not cause permanent damage, but it may pay to observe the total of a past 
order before and after the change to check that the totals dont change.

**Be careful** *when removing modifier types from the system, that is removing them completely from the code base, where running
dev/build would remove them from the database. This may cause the modifier table to be removed from the database, which could
remove historical data from your site.*