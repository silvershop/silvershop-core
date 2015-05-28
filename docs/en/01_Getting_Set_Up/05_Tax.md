# Tax

Tax calculation is often required by governments when selling items through an online shop.
To enable tax calculations, you'll need to introduce an appropriate [order modifier](../03_How_It_Works/Order_Modifiers.md).


Modifiers included with core code:

 * FlatTaxModifier - addes a set percentage to all orders
 * GlobalTaxModifier - applies different tax calculation to different regions

Common requirements:

 * Specify if the tax is inclusive or exclusive.
 * Tax calculation is appropriate to country.
 * Different tax rates for different products.