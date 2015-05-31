# Architecture

## Basics to understand

[ShoppingCart](Shopping_Cart.md) provides the API to add and remove items from the current order.

Products are pages, with some extra details, such as an image, and price.

Under the hood, a cart and an order are both the same DataObject (Order), but an order is treated as 'cart', 
when it has the status 'cart'.
Once an order is processed on the CheckoutPage, it is no longer a cart, and has some other status.

A product is associated with an order/cart as an OrderItem. An order item usually has a quantity.

## Data Model

Here are some diagrams:
[DataModel PDF v0.8](https://github.com/downloads/burnbright/silverstripe-shop/ShopModule0.8.pdf)


### DataObjects

Here is an overview of the model classes

  * Order
   * OrderAttribute
  	* OrderItem
  	 * Product_OrderItem
   	 * ProductVariation_OrderItem
   * OrderModifier - Shippping/Tax calculators etc... see [order modifiers](Order_Modifiers.md)
   * ProductVariation - variations in price/colour/shape etc for a product.
   
   * OrderStatusLog

### Page Types

  * Product - view product details
  * ProductGroup - browse and find products via categories
  * CartPage - display and edit the cart
  * CheckoutPage - place an order
  * AccountPage - Member functionality

### Decorators

 * ShopMember - adds shipping details to a Member.

### Shopping Cart

Cart items are stored in the database. A new order is created when the first item is added to the cart.

### Checkout

The checkout is a page type which includes an OrderForm, plus has the facility for displaying an order that has been placed. 

Also see [core changes](Core_Changes.md).

### Persisting order information

If you are changing your product catalog or customers are updating / removing their member info, then you don't want to loose
important information in the process.

Protections that are in place:

 * Products are pages, so inherently have versions associated with them. The product ID and product version ID are associated 
 with an order item from the moment they are added to the cart.
 * Product Variations are versioned, however their associated attribute types + values are not protected.
 * OrderItem amount is recalculated on write after an order has been placed (no longer in cart). You should be careful not to
 write order items if they are storing historical values that shouldn't be changed.