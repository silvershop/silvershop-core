Here are the subclasses, and extensions of standard SilverStripe Framework components for the shop module.

 * `SilverShop\ORM\FieldType\ShopCurrency` - provides ability to customise currency formatting.
 * `SilverShop\Admin\ProductBulkLoader` - extends CSVBulkLoader to provide shop-specific loading. See [Bulk Loading](../01_Getting_Set_Up/Bulk_Loading.md) for more.
 * `SilverShop\Dev\ShopDevelopmentAdmin` - extends DevelopmentAdmin so that we can call mysite/dev/shop
 * `SilverShop\ORM\FieldType\I18nDatetime` - provides I18n formating for dates.
