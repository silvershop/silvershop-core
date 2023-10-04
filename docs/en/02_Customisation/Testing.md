Testing is highly important for maintaining a quality product.

## Unit Testing

We insist that the shop module contains a suite of unit tests that are updated with any changes to the code. Sub-Modules should also have their own test suites.
See the [framework testing documentation](http://docs.silverstripe.org/en/developer_guides/testing/) for setup information etc.
See also: [https://docs.phpunit.de/en/9.6/](https://docs.phpunit.de/en/9.6/)

To run all shop tests visit `yoursite/dev/tests/module/shop`. If you intend on doing lots of development, it might be a good idea to run tests from the command line.

**Note:** Be aware that your website's configuration may affect your test results. The tests should set configuration correctly, but sometimes configuration options get missed.

Every change to this module is [![tested using a Continuous Integration service](https://github.com/silvershop/silvershop-core/actions/workflows/ci.yml/badge.svg)](https://github.com/silvershop/silvershop-core/actions/workflows/ci.yml).

### Writing Tests

The products created by the shop.yml file are all in draft form (unpublished). You need to publish products if you wish to test functionality that involves adding products to the cart etc.

## Regression Testing

As features/sites are being developed, they should be tested. In a nutshell, be sure to test the following main functionality:

 * Adding/removing items from cart
 * Placing an order. Follow the process end-to-end.
