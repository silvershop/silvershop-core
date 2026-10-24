This page is intended to make you aware of upgrade issues you may face, and how to resolve them.

Don't forget to run the following url commands when you upgrade the shop module:

    [yourdomain.com]/dev/build?flush=all
    [yourdomain.com]/dev/tasks/ShopMigrationTask

# 6.3

## Countries and states come from `commerceguys/addressing`

The country list and the subdivisions (states, provinces, counties) offered on address forms now come from
[commerceguys/addressing](https://github.com/commerceguys/addressing), which packages CLDR and Google's address
data. Country names are shown in the current locale.

* `_config/countries.yml` has been removed. `ShopConfigExtension.iso_3166_country_codes` still works, but only
  needs the countries you want to add or rename, e.g.

  ```yaml
  SilverShop\Extension\ShopConfigExtension:
    iso_3166_country_codes:
      NZ: 'Aotearoa New Zealand'
  ```

* `Address.State` is now a `SilverShop\Forms\SubdivisionField`. When the selected country has known subdivisions
  it's a dropdown storing the value written on an address (`NSW`, `CA`, `Co. Cork`); otherwise it stays a text
  field. The field swaps automatically when the country changes, using the `api/v1/subdivisions/{country}.json`
  endpoint. Existing free-text values are kept, and pre-selected when they match a subdivision name.
* Customise the field with the `updateStateField` extension hook on `Address`, or get the data directly from
  `SilverShop\Model\AddressData`.

## `Order::Total()` is deprecated

`Order::Total()` is deprecated and will be removed in 7.0. The method shadows the `Total` database field, so
anything that looks the value up by name (templates, `obj('Total')`, GridField columns, CSV exports) gets a raw
number instead of the field's `Currency` casting, which gets in the way of formatting the order total.

Calling it now raises a deprecation notice (when [deprecation notices](https://docs.silverstripe.org/en/6/upgrading/deprecations/)
are enabled). Update your code as follows:

* PHP: replace `$order->Total()` with `$order->GrandTotal()`, or `$order->getField('Total')` / `$order->Total`
  when you want the raw database value.
* Templates rendered with an `Order` (or `$Cart`): replace `$Total` with `$GrandTotal`, e.g. `$GrandTotal.Nice`.
  The core templates have already been updated, so copy the change into any templates you override, such as
  `Cart.ss`, `Order.ss`, `Order_Content_SubTotals.ss`, `Order_EmailPlainBody.ss` and `OrderHistory.ss`.

This only applies to `Order`. `OrderItem::Total()` and `OrderModifier::Total()` are unchanged.

Once the method is removed in 7.0, `$order->Total` and `$Total` in templates will resolve to the `Currency` database
field as normal.

# 2.0
## Payment Gateway Settings
Your payment gateway parameters need to be set on the GatewayInfo class.  See [Silverstripe-Omnipay 2 Upgrading Instructions](https://github.com/silverstripe/silverstripe-omnipay/blob/master/docs/changelogs/2.0.md#configuration-api) for details.

In addition, if overriding the name of your payment gateway within a language file then switch the Payment class for the Gateway class.  For example, in `en.yml`:
```yaml
en:
  Gateway:
    NameOfPaymentGateway: 'Credit Card'
```
## Decimal Places
The Product pricing and measurement properties have generous decimal place settings.  Customise as required in `config.yml`.  Example below:
```yaml
Product:
  db:
    BasePrice: 'Currency(19,2)'
    Weight: 'Decimal(12,3)'
    Height: 'Decimal(12,3)'
    Width: 'Decimal(12,3)'
    Depth: 'Decimal(12,3)'
```

## Translations no longer appear

You translated your shop to another language than english, now your translations no longer work? This is, because a lot of the translation keys have changed to be compatible with the new SilverStripe translation syntax. Instead of using `<% _t('KEY','VALUE') %>` calls in templates, one should now use: `<%t KEY 'VALUE' %>` instead.

It's best to look at the current shop templates and adjust your custom templates accordingly. You can also look at the language files within the `silvershop/lang` folder to get an idea of the current translation keys/values.

Sadly, there's no automatic task that will fix these issues for you.

# 1.0

## Images are lost

This is because Product images are now Image DataObjects, instead of Product_Image DataObjects.
Try running the ShopMigrationTask.

## 'FeaturedProduct' field renamed to 'Featured' on Product model

## OrderForm checkout template variable has been replaced with Form

Also note that the OrderForm class is gone, and we now have CheckoutForm.

# 0.9

## Prices are missing

I've renamed Price database field to BasePrice, as part of work to make pricing system more flexible.
Try running the ShopMigrationTask.

# 0.8.4 -0.8.5

## Variation Attribute Types are lost

The Product->VariataionAttributes() relationship was renamed to Product->VariationAttributeTypes()
Try running mysite/dev/tasks/ShopMigrationTask, or if that fails, rename the database table manually:
*Product_VariationAttributes* becomes *Product_VariationAttributeTypes*

## CSS is lost / templates 

CSS files are now expected to be included by templates, or by your own
page / decorator requirements. This change was made to allow flexibility
of what css files to include. All the default templates now have requires
statements like this:

    <% require themedCSS(product,shop) %>
    
To fix in your site, update your templates to include the appropriate css
files as per above. If you want a more advanced solution, you could
add requirements calls to your Page_Controller init function, or
use an extension.

## Modifiers breaking

If you have written custom modifiers, or are using old modifiers, they may stop working
properly.

 * Values not showing up
 * Values not being added to total
 
If the modifier has an Amount field, this should be deprecated and replaced with
a value field, which takes an $incoming argument.


# older / other

## Payment values / amounts have gone missing

The payment module switched the "Amount" field from using the Currency DBField to Money. Money
is a CompositeDBField that combines AmountAmmount and AmountCurrency. The "Currency" field
was also dropped. [Related diff](https://github.com/silverstripe-labs/silverstripe-payment/commit/8f27918294ac34b688f137e36b424616df55dd7f#diff-4)

To fix: rename your database Amount column to AmountAmount, and your Currency column to AmountCurrency.
