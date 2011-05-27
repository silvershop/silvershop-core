# Order Modifiers

## What are modifiers?

A cart holds two basic items: Order Items and Order Modifiers.  The former are the actual products. The latter are items that add / change / deduct / do whatever to the order.  One of the main differences between Order Items and Modifiers is that an Order Item has a quantity associated to it. 

Being able to write your own Order Modifiers is a powerful way to adjust your cart with things like: bonus products, tax, delivery charges, staff discounts, etc...


## How to add a modifier

  * create a new class "MyModifierOne" that extends the OrderModifier class
  * use Order::set_modifiers(array("MyModifierOne", "MyModifierTwo")); in your _config file to "include" the modifier in all carts. 


## Extending the modifier class

The best way to write a modifier is to copy the modifier class itself and 

  * delete any methods that should stay the same
  * keep + adjust any methods that are different
  * add any other (protected / private) methods and (static) variables that you may need to use. 

There are lots of comments in the code that will help you.  You may also want to review two other classes:

  * OrderAttribute (you may want to overload some of the methods)
  * Other OrderModifier sub-classes