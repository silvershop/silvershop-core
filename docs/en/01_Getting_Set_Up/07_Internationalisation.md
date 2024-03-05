# Internationalsiation i18n

## Setting different currency, decimal and thousand separators

You can set the currency, decimal and thousand separators in your config.yml file.

```yaml
SilverShop\Extension\ShopConfigExtension:
    base_currency: 'EUR'
SilverShop\ORM\FieldType\ShopCurrency:
  decimal_delimiter: ','
  thousand_delimiter: '.'
  # european style currencies, e.g. 45,00 € instead of €45,00
  append_symbol: true
  ```

## Currencies are shown as simple number or with wrong decimal separator in GridFields

See [Troubleshooting](03_Troubleshooting.md#currencies-are-shown-as-simple-number-or-with-wrong-decimal-separator-in-gridfields) for more information.
