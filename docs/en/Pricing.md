# Shop Prices

You may want to create more complex pricing calculations than product x quantity.
Infact you may have very complex price calculation requirements.

There are many monetary values to consider in a shop system. This page aims
to list and explain them.

## Products

Multiple prices can be displayed on a product page, these may include:

 * Original price
 * Variation price
 * Recommended retail price
 * Discounted price
 	- temporary reduction
 	- 'your' group discount
 * Discount amount (total savings)
 * Tax inclusive or exclusive price
 * Currency-converted price
 * Name/choose your price

Store owners may also want to keep track of product _cost price_ so that
total revenue/income can be calculated.

## Order Items

 * Multi-dimension calculation - eg selling square / volume areas
 * Discount coupon - enter a voucher, coupon, gift code
 * Tiered pricing - ie bulk discounts based on quantities

See also: DiscountModule

## Orders
 
 * Items Subtotal
 * Discounts - coupons, credit notes
 * Shipping, handling
 * Tax
 * Grand Total
 
## Payments

 * Amount

## Automatically applying mass reductions

Trigger reductions to occur on a cateogry / set of products during a specific period.

## Rounding

Values are rounded to two decimal places using the default php [round function](http://php.net/manual/en/function.round.php),
which uses the 'round half up' mode by default.

The Currency db field only allows storing up to two decimal values.