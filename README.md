# Shop Module

The SilverStripe Shop module aims to provide developers with a framework for building, and customising ecommerce-based projects.
It includes facilities for customers to browse products and place orders, and for administrators to manage products and orders.
We've put a strong focus on testing, and thanks to TravisCI, you can see the [build status](https://travis-ci.org/burnbright/silverstripe-shop) of this project, running on MySQL, SQLite, Postgres, as well as a few different versions of PHP.

[![Latest Stable Version](https://poser.pugx.org/burnbright/silverstripe-shop/v/stable.png)](https://packagist.org/packages/burnbright/silverstripe-shop)
[![Latest Unstable Version](https://poser.pugx.org/burnbright/silverstripe-shop/v/unstable.png)](https://packagist.org/packages/burnbright/silverstripe-shop)
[![Build Status](https://secure.travis-ci.org/burnbright/silverstripe-shop.png)](http://travis-ci.org/burnbright/silverstripe-shop)
[![Code Coverage](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/badges/coverage.png?s=1abe84b468ef3d96646a0546954adba8131d6459)](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/badges/quality-score.png?s=d60753d6cca3817e80aca3dbb79eb5bd4140c981)](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/)
[![Total Downloads](https://poser.pugx.org/burnbright/silverstripe-shop/downloads.png)](https://packagist.org/packages/burnbright/silverstripe-shop)

Website: http://ss-shop.org
Demo: http://demo.ss-shop.org

Your contributions, and feedback are welcomed and appreciated. There are many ways you can [contribute to this project](https://github.com/burnbright/silverstripe-shop/wiki/Contributing).
A tremendous thanks to [everyone that has already contributed](https://github.com/burnbright/silverstripe-shop/graphs/contributors).

## Maintainer Contact

 * Jeremy Shipman (Jedateach, jeremy@burnbright.net)

## Requirements

 * SilverStripe 3 [framework](https://github.com/silverstripe/silverstripe-framework) & [cms](https://github.com/silverstripe/silverstripe-cms)
 * [Omnipay Module](https://github.com/burnbright/silverstripe-omnipay) + it's dependencies.

## Documentation

 * http://demo.ss-shop.org/docs - for Developers & Users
 * http://api.ss-shop.org - API

## Installation Instructions

To install silverstripe + shop into a directory called 'myshop', using [composer](http://doc.silverstripe.org/framework/en/installation/composer), run the following commands:
```
composer create-project silverstripe/installer myshop
composer require -d myshop "burnbright/silverstripe-shop:dev-master"
```

There are a few useful tasks that can be run via a url to help you test:

 * `{yoursite.com}/dev/tasks/PopulateShopTask` - will create cart, checkout, account, category and product pages
 * `{yoursite.com}/dev/tasks/PopulateCartTask` - will add products to the cart, and navitate you to the checkout

## Configuration

You can view various configuration options in the 'example_config.yml' file.

## Migrating

Visit [yoursite]/dev/tasks/ShopMigrationTask to migrate your database to work properly.
Make sure you take a database backup, as perfect results aren't guaranteed.
