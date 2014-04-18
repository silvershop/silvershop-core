# Testing the shop module

Testing is highly important for maintaining a quality product.

## Regression Testing

This [Google doc containing regression tests](https://spreadsheets.google.com/ccc?key=0AtHUrSaBxJY8dG8teWVNTFYzbThZYUhhLTNmT0FiUHc&hl=en) will give you an idea of the various things you can test manually.
In a nutshell, be sure to test the following main functionality:

 * Adding/removing items from cart
 * Placing an order

## Unit / Functional Testing

We insist that the shop module contains a suite of unit tests that are updated with any changes to the code. Sub-Modules should also have their own test suites.
See the [sapphire testing documentation](http://doc.silverstripe.org/sapphire/en/topics/testing/index) for setup information etc.
See also: [http://www.phpunit.de/manual/3.5/en/index.html](http://www.phpunit.de/manual/3.5/en/index.html)

To run all shop tests visit yoursite/dev/tests/module/shop.

**Note:** Be aware that your configuration in _config.php may affect your test results.

### Writing Tests

The products created by the shop.yml file are all in draft form (unpublished). You need to publish products if you wish to test
functionality that involves adding products to the cart etc.