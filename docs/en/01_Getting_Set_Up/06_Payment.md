Online payment is a critical part of any ecommerce solution. This module makes use of the PHP Omnipay payment library, which does a great job of standardising online payments.

This module integrates with the omnipay library via the [SilverStripe-Omnipay module](https://github.com/burnbright/silverstripe-omnipay). It is automatically required by the shop module, via composer.

This Omnipay presentation gives a general overview to online payments and integrating omnipay with SilverStripe: http://jeremyshipman.com/blog/my-2c-on-omnipay-integrating-with-silverstripe/

## Available payment types

There are a number of [Omnipay payment drivers that come with omnipay out of the box](https://github.com/thephpleague/omnipay/tree/1.1#payment-gateways).

The best way to find additional Omnipay payment drivers is perhaps to do a search on Packagist: https://packagist.org/search/?q=omnipay

Here is a tutorial for [setting up PayPal via omnipay](https://github.com/burnbright/silverstripe-omnipay/blob/master/docs/en/PayPalExpressSetup.md).


