<div class="bad" markdown="1">
This feature is currently not working / complete.
See the [issue on github](https://github.com/burnbright/silverstripe-shop/issues/2).
</div>


## Multi Language / Translated Shop

Making your shop support multiple languages is much like doing the same for any other
SilverStripe website. [Here is a guide](http://www.balbuss.com/setting-up-a-multilingual-site/).

Here is some shop-specific information:

Add the following to your _config.php file:

	:::php
	Object::add_extension('SiteTree', 'Translatable');
	Object::add_extension('SiteConfig', 'Translatable');
	Object::add_extension('ProductVariation', 'Translatable');
