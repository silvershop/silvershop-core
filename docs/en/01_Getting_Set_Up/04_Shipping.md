# Shipping

Shipping calculations can be introduced to an order with an [order modifier](../03_How_It_Works/Order_Modifiers.md).

Modifiers included with core code:

 * FreeShippingModifier
 * PickupShippingModifier
 * SimpleShippingModifier
 * WeightShippingModifier

There are more advanced shipping options available via submodules:
https://github.com/burnbright/silverstripe-shop-shipping

Common requirements for shipping are:

 * Change price, depending on location.
 * Alternatively disallow shipping to certian locations, eg: specific countries, or rural addresses.
 * Allow visitor to choose different options, affecting things like delivery time, insurance, tracking, etc
 * Calculate delivery price, based on: weight, volume, quantity, and value, or various combinations of.
 * Ship to a different address from the billed/invoiced address.
 * Allow pickup from store, in which case no charge is applied to order.

