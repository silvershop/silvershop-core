# Shop Module

The SilverStripe Shop module aims to provide developers with a framework for building, and customising ecommerce-based projects.
It includes facilities for customers to browse products and place orders, and for administrators to manage products and orders.
 
[![Build Status](https://secure.travis-ci.org/burnbright/silverstripe-shop.png?branch=1.0)](http://travis-ci.org/burnbright/silverstripe-shop)

Website: http://ss-shop.org
Demo: http://demo.ss-shop.org

We are looking for contributiors. There are many ways you can [contribute to this project](https://github.com/burnbright/silverstripe-shop/wiki/Contributing).
A tremendous thanks to [everyone that has already contributed](https://github.com/burnbright/silverstripe-shop/graphs/contributors).

## Maintainer Contact

 * Jeremy Shipman (Jedateach, jeremy@burnbright.net)

## Requirements

 * SilverStripe 3+ [sapphire](https://github.com/silverstripe/sapphire) & [cms](https://github.com/silverstripe/silverstripe-cms)
 * [Payment Module 0.3+](https://github.com/silverstripe-labs/silverstripe-payment)

## Documentation

 * http://demo.ss-shop.org/docs - for Developers & Users
 * http://api.ss-shop.org - API

## Installation Instructions

Put the module folder into your SilverStripe root folder.

Make sure the module root folder is named 'shop' to ensure requirements
work properly.

## Configuration

You can view various configuration options in the 'example_config.php' file.

*WARNING:* do not copy and paste entire configuration example file without
first understanding each line, otherwise the system may not work as documented.
In other words, only copy the lines which you need and understand.

## Migrating

Visit [yoursite]/dev/tasks/ShopMigrationTask to migrate your database to work properly.
Make sure you take a database backup, as perfect results aren't guaranteed.
