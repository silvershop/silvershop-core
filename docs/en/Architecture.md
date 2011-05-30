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


Also see [core changes](CoreChanges).