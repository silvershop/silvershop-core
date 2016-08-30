## Installation
In a terminal window, in the website root, type:
```sh
composer require silvershop/core
```

## Configuration Options
The `example_config.yml` file gives an exhaustive list of the possible configuration options within the Silvershop module.  Copy and paste this to `mysite/config/shop.yml` and adjust.  See the [customisation](../02_Customisation/index.md) section of this documentation for more details.

## Email Setup
By default Silvershop uses [Silverstripe Email Helpers](https://github.com/markguinn/silverstripe-email-helpers) to send emails.  Follow the [Configuration Instructions](https://github.com/markguinn/silverstripe-email-helpers#smtp-mailer) to setup the smtp mailer.

## Demo in Localhost
It is always a good idea to have a demo on hand.  Test data can be loaded by visiting `{yoursite.com}/dev/tasks/PopulateShopTask`.
A link to the Shop will now be available in the global navigation.

