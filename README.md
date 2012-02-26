# Shop Module

## Maintainer Contact

 * Jeremy Shipman (Jedateach, jeremy@burnbright.net)

## Requirements

 * SilverStripe 2.4+
 * Payment Module 0.3+

## Documentation

 * http://ss-shop.org/docs
 * http://api.ss-shop.org

## Installation Instructions

1. Find out how to add modules to SilverStripe and add module as per usual.

2. Copy configurations from this module's _config.php file
into mysite/_config.php file and edit settings as required.
NB. the idea is not to edit this module so that you can
upgrade this module in one go without redoing the settings.
Instead customise your application using your mysite folder.

Make sure the module root folder is named 'shop' to ensure requirements
work properly.

## Configuration

You can view various configuration options in the 'example_config.php' file.

WARNING: do not copy and paste entire configuration example file without
first understanding each line, otherwise the system may not work as documented.
In other words, only copy the lines which you need and understand.

When running dev/build/ add: ?updatepayment=1 to migrate
payment data from 2.3 to 2.4 style (currency db field to
money db field).
