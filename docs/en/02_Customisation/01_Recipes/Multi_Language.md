# Multi Language / Translated Shop

Making your shop support multiple languages is much like doing the same for any other
SilverStripe website. [Here is a guide](http://www.balbuss.com/setting-up-a-multilingual-site/).

You can use the [Fluent](https://packagist.org/packages/tractorcow/silverstripe-fluent) Module to translate your shop.


## Adding translations

If you introduce a new string, add it to `en.yml` manually.
If you insist on using a text collector, you could use
<https://github.com/Zauberfisch/silverstripe-better-i18n>


## Transifex

Once you make changes to `en.yml`, these need to be pushed up to
[Transifex](http://transifex.com/silvershop/silverstripe-shop/).

Similarily, translations done on Transifex need to be pulled back down.

**Pushing and pulling can only be done by Transifex maintainers**. Poke one of those, if you feel
you need access.


You then need to [install the transifex client](http://docs.transifex.com/client/setup/),
then [configure your access credentials](http://docs.transifex.com/client/config/)

### Pushing

Use `tx push -s` to push the changed source file to transifex.


### Pulling

Once translations are done on transifex, you can grab the translations with
`tx pull -a` and commit them.


