Architecture
=============

[ShoppingCart](ShoppingCart) provides the API to add and remove items from the current order. Once an order is processed on the CheckoutPage, it is no longer in the cart.

## Data Model

Here are some diagrams: 
[DataModel PDF v1.0alpha](https://code.google.com/p/silverstripe-ecommerce/downloads/detail?name=SSEcommerce1.0alpha.pdf&can=2),
[DataModel PDF v0.6.1](https://code.google.com/p/silverstripe-ecommerce/downloads/detail?name=SSEcommerce0.61.pdf&can=2&q=)

### DataObjects

  * Order
   * OrderAttribute
  	* OrderItem
  	 * Product_OrderItem
   	 * ProductVariation_OrderItem
   * OrderModifier - Shippping/Tax calculators etc... see [order modifiers](OrderModifiers)
   * ProductVariation - variations in price/colour/shape etc for a product.
   
   * OrderStatusLog

### Page Types

  * Product
  * CartPage
  * CheckoutPage
  * AccountPage - Member functionality

### Decorators

 * EcommercePayment - adds connection to an order for each Payment.
 * EcommerceRole - adds shipping details to a Member.

### Shopping Cart

Cart items are stored in the database. A new order is created when the first item is added to the cart.

### Checkout

The checkout is a page type which includes an OrderForm, plus has the facility for displaying an order that has been placed. 

Also see [core changes](CoreChanges).

### Persisting order information

If you are changing your product catalog or customers are updating / removing their member info, then you don't want to loose important information in the process.

Protections that are in place:

 * Products are pages, so inherently have versions associated with them. The product ID and product version ID are associated with an order item from the moment they are added to the cart.
 * Product Variations are versioned, however their associated attribute types + values are not protected.
 * OrderItem amount is recalculated on write after an order has been placed (no longer in cart). You should be careful not to write order items if they are storing historical values that shouldn't be changed.