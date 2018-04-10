# Order Modifiers

A cart holds two basic items: 

 * __Items__ - the actual product lines in an order, which usually contain a quantity and unit price.
 * __Modifiers__ - additional lines that specify costs or deductions on the items subtotal.

They provide an abstract way to add additional (non-product) lines to your order.
Other shopping cart soultions usually handle this part of the system with a hard-coded sum of shipping, tax etc.
The oder modifiers system should provide more flexability around the footer lines for an order.

The OrderModifier class is a base datamodel for creating specific modifiers with.

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


## Shipping & Tax Modifiers

 * `SilverShop\Model\Modifiers\Shipping\Simple` -Allows you to choose different flat rates for different countries.
 * `SilverShop\Model\Modifiers\Shipping\Weight` - Requires entering a weight for each product.
 * `SilverShop\Model\Modifiers\Tax\FlatTax`
 * `SilverShop\Model\Modifiers\Tax\Base`

## Add an Order Modifier

Modifiers are introduced using the SilverStripe config system:

```yaml
SilverShop\Model\Order:
  modifiers:
    - 'SilverShop\Model\Modifiers\Shipping\Simple'
    - 'SilverShop\Model\Modifiers\Tax\FlatTax'
```

Note that you may need to clear the current order to see updates to modifiers. You can do this by visiting `mysite.com/shoppingcart/clear`
  
## Removing an Order Modifier

Removing modifiers from the modifiers array should not cause permanent damage, but it may pay to observe the total of a past 
order before and after the change to check that the totals dont change.

**Be careful** *when removing modifier types from the system, that is removing them completely from the code base, where running dev/build would remove them from the database. This may cause the modifier table to be removed from the database, which could remove historical data from your site.*

## Developing New Modifiers

The best way to write a modifier is to copy the modifier class itself and 

  * delete any methods that should stay the same
  * keep + adjust any methods that are different
  * add any other (protected / private) methods and (static) variables that you may need to use. 

There are lots of comments in the code that will help you.  You may also want to review two other classes:

  * OrderAttribute (you may want to overload some of the methods)
  * Other OrderModifier sub-classes
