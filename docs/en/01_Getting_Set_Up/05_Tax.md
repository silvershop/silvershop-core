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

## Product-specific tax rates (FlatTax)

When using `SilverShop\Model\Modifiers\Tax\FlatTax`, products can optionally be assigned to a
`TaxClass` on the **Pricing** tab in the CMS.

- Leave `TaxClass` empty to use the default `FlatTax.rate` value.
- Use a tax class with `Rate = 0` for tax-exempt items.
- Use a tax class with `Rate = 0.15` for 15% tax.

Example:

- Product A (food): TaxClass rate `0` (0% tax)
- Product B (general goods): TaxClass rate `0.15` (15% tax)

For an order containing one of each, tax is calculated per item using those rates.
