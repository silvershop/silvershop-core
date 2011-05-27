Migrate info from [here](http://code.google.com/p/silverstripe-ecommerce/wiki/HowEcommerceIsBuilt)

[ShoppingCart](ShoppingCart) provides the API to add and remove items from the current order.

Once an order is processed on the CheckoutPage, it is no longer in the cart.

Each user shopping cart is stored in the session, and saved to the database when the order payment is processed.

# Data Model

Here are some diagrams: 
[DataModel PDF v1.0alpha](https://code.google.com/p/silverstripe-ecommerce/downloads/detail?name=SSEcommerce1.0alpha.pdf&can=2),
[DataModel PDF v0.6.1](https://code.google.com/p/silverstripe-ecommerce/downloads/detail?name=SSEcommerce0.61.pdf&can=2&q=)

## DataObjects

  * Order
   * OrderAttribute
  	* OrderItem
  	 * Product_OrderItem
   	 * ProductVariation_OrderItem
   * OrderModifier
   * ProductVariation
   
   * OrderStatusLog

## Page Types

  * Product
  * CartPage
  * CheckoutPage
  * AccountPage

## Decorators

 * EcommercePayment - adds connection to an order for each Payment.
 * EcommerceRole - adds shipping details to a Member.

## Shopping Cart

Cart items are stored in the database. A new order is created when the first item is added to the cart.

## Checkout


## Core Customisations/Extensions

Explaining why we've made these changes.

EcommerceResponse / CartResponse
ModelAdminBaseClass