# Core Changes

Here are the subclasses, and extensions of standard Sapphire components for the eCommerce module.


 * EcommerceCurrency - provides ability to customise currency formatting.
 * EcommercePayment - provides some additional functionality that should eventually move to Payment itself.
 * ProductBulkLoader - extends CSVBulkLoader to provide ecommerce-specific loading. See [Bulk Loading](BulkLoading) for more.

 * EcommerceSiteTreeExtension - adds a few functions to SiteTree to give each page some e-commerce related functionality.

 * EcommerceResponse, CartResponse

 * EcommerceDevelopmentAdminDecorator - extends DevelopmentAdmin so that we can call mysite/dev/ecommerce


 * OptionalConfirmedPasswordField - requires entering a password twice to ensure it is correct.
 
 * I18nDatetime