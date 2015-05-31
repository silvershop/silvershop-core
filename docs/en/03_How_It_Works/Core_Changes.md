Here are the subclasses, and extensions of standard SilverStripe Framework components for the shop module.

 * `ShopCurrency` - provides ability to customise currency formatting.
 * `ProductBulkLoader` - extends CSVBulkLoader to provide shop-specific loading. See [Bulk Loading](../01_Getting_Set_Up/Bulk_Loading.md) for more.
 * `ShopDevelopmentAdmin` - extends DevelopmentAdmin so that we can call mysite/dev/shop
 * `OptionalConfirmedPasswordField` - requires entering a password twice to ensure it is correct.
 * `I18nDatetime` - provides I18n formating for dates.