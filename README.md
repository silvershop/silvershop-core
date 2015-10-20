# SilverStripe Shop Module

The SilverStripe Shop module aims to provide developers with a framework for building, and customising ecommerce-based projects.
It includes facilities for customers to browse products and place orders, and for administrators to manage products and orders.
We've put a strong focus on testing, and thanks to TravisCI, you can see the [build status](https://travis-ci.org/burnbright/silverstripe-shop) of this project, running on MySQL, SQLite, Postgres, as well as a few different versions of PHP.

[![Latest Stable Version](https://poser.pugx.org/burnbright/silverstripe-shop/v/stable.png)](https://packagist.org/packages/burnbright/silverstripe-shop)
[![Latest Unstable Version](https://poser.pugx.org/burnbright/silverstripe-shop/v/unstable.png)](https://packagist.org/packages/burnbright/silverstripe-shop)
[![Build Status](https://travis-ci.org/burnbright/silverstripe-shop.svg?branch=master)](http://travis-ci.org/burnbright/silverstripe-shop)
[![Code Coverage](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/badges/coverage.png?s=1abe84b468ef3d96646a0546954adba8131d6459)](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/badges/quality-score.png?s=d60753d6cca3817e80aca3dbb79eb5bd4140c981)](https://scrutinizer-ci.com/g/burnbright/silverstripe-shop/)
[![Total Downloads](https://poser.pugx.org/burnbright/silverstripe-shop/downloads.png)](https://packagist.org/packages/burnbright/silverstripe-shop)

Live chat on Gitter! [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/burnbright/silverstripe-shop?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Website: http://ss-shop.org
Demo: http://demo.ss-shop.org

Your contributions, and feedback are welcomed and appreciated. There are many ways you can [contribute to this project](https://github.com/burnbright/silverstripe-shop/wiki/Contributing).
A tremendous thanks to [everyone that has already contributed](https://github.com/burnbright/silverstripe-shop/graphs/contributors).

## Requirements

 * SilverStripe 3.1 or higher [framework](https://github.com/silverstripe/silverstripe-framework) & [cms](https://github.com/silverstripe/silverstripe-cms)
 * [Omnipay Module](https://github.com/burnbright/silverstripe-omnipay) + it's dependencies.

See `composer.json` for exact set of dependencies.

## Documentation

 * http://demo.ss-shop.org/docs - for Developers & Users

## Installation Instructions

To install silverstripe + shop into a directory called 'myshop', using [composer](http://doc.silverstripe.org/framework/en/installation/composer), run the following commands:
```
composer create-project silverstripe/installer myshop
composer require -d myshop "burnbright/silverstripe-shop:dev-master"
```

### Migrating

Visit [yoursite]/dev/tasks/ShopMigrationTask to migrate your database to work properly.
Make sure you take a database backup, as perfect results aren't guaranteed.

There are a few useful tasks that can be run via a url to help you test:

 * `{yoursite.com}/dev/tasks/PopulateShopTask` - will create cart, checkout, account, category and product pages
 * `{yoursite.com}/dev/tasks/PopulateCartTask` - will add products to the cart, and navitate you to the checkout

## Configuration

You can view various configuration options in the 'example_config.yml' file.

## Core Features

 * Product Catalog - Products extend Page, and can be browsed within Product Category pages.
 * Cart Page - For viewing and updating your cart.
 * Checkout - Gather delivery/billing details and anything specific to the order. Can be single-page or multi-step.
 * Online Payments - Via the omnipay module.
 * Administration - Manage the catalog and orders in the CMS.

Futher functionality is provided by add-on submodules.

## Add-on Sub Modules

Don't reinvent the wheel! Get additional pre-built functionality with these sub modules:

submodule | github | add-ons | packagist
----------|--------|---------|----------
burnbright/silverstripe-shop-coloredvariations | [github](http://www.github.com/burnbright/silverstripe-shop-coloredvariations) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-coloredvariations) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-coloredvariations)
burnbright/silverstripe-shop-comparison | [github](http://www.github.com/burnbright/silverstripe-shop-comparison) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-comparison) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-comparison)
burnbright/silverstripe-shop-discount | [github](http://www.github.com/burnbright/silverstripe-shop-discount) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-discount) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-discount)
burnbright/silverstripe-shop-dispatchit | [github](http://www.github.com/burnbright/silverstripe-shop-dispatchit) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-dispatchit) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-dispatchit)
burnbright/silverstripe-shop-enquiry | [github](http://www.github.com/burnbright/silverstripe-shop-enquiry) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-enquiry) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-enquiry)
burnbright/silverstripe-shop-geocoding | [github](http://www.github.com/burnbright/silverstripe-shop-geocoding) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-geocoding) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-geocoding)
burnbright/silverstripe-shop-googleanalytics | [github](http://www.github.com/burnbright/silverstripe-shop-googleanalytics) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-googleanalytics) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-googleanalytics)
burnbright/silverstripe-shop-productfinder | [github](http://www.github.com/burnbright/silverstripe-shop-productfinder) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-productfinder) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-productfinder)
burnbright/silverstripe-shop-shipping | [github](http://www.github.com/burnbright/silverstripe-shop-shipping) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-shipping) | [packagist](https://packagist.org/packages/burnbright/silverstripe-shop-shipping)
markguinn/silverstripe-shop-search | [github](http://www.github.com/markguinn/silverstripe-shop-search) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-search) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-search)
markguinn/silverstripe-shop-extendedpricing | [github](http://www.github.com/markguinn/silverstripe-shop-extendedpricing) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-extendedpricing) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-extendedpricing)
markguinn/silverstripe-shop-extendedimages | [github](http://www.github.com/markguinn/silverstripe-shop-extendedimages) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-extendedimages) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-extendedimages)
markguinn/silverstripe-shop-livepub | [github](http://www.github.com/markguinn/silverstripe-shop-livepub) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-livepub) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-livepub)
tylerkidd/silverstripe-shop-google-base | [github](http://www.github.com/tylerkidd/silverstripe-shop-google-base) | [add-ons](http://addons.silverstripe.org/add-ons/tylerkidd/silverstripe-shop-google-base) | [packagist](https://packagist.org/packages/tylerkidd/silverstripe-shop-google-base)
webtorque7/silverstripe-shop-shipping-matrix | [github](http://www.github.com/webtorque7/silverstripe-shop-shipping-matrix) | [add-ons](http://addons.silverstripe.org/add-ons/webtorque7/silverstripe-shop-shipping-matrix) | [packagist](https://packagist.org/packages/webtorque7/silverstripe-shop-shipping-matrix)
markguinn/silverstripe-shop-downloadable | [github](http://www.github.com/markguinn/silverstripe-shop-downloadable) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-downloadable) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-downloadable)
markguinn/silverstripe-shop-groupedproducts | [github](http://www.github.com/markguinn/silverstripe-shop-groupedproducts) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-groupedproducts) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-groupedproducts)
markguinn/silverstripe-shop-ajax | [github](http://www.github.com/markguinn/silverstripe-shop-ajax) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-shop-ajax) | [packagist](https://packagist.org/packages/markguinn/silverstripe-shop-ajax)
milkyway-multimedia/ss-shop-recommended | [github](http://www.github.com/milkyway-multimedia/ss-shop-recommended) | [add-ons](http://addons.silverstripe.org/add-ons/milkyway-multimedia/ss-shop-recommended) | [packagist](https://packagist.org/packages/milkyway-multimedia/ss-shop-recommended)
clintLandrum/silverstripe-productreviews | [github](http://www.github.com/clintLandrum/silverstripe-productreviews) | [add-ons](http://addons.silverstripe.org/add-ons/clintLandrum/silverstripe-productreviews) | [packagist](https://packagist.org/packages/clintLandrum/silverstripe-productreviews)
milkyway-multimedia/ss-shop-inventory | [github](http://www.github.com/milkyway-multimedia/ss-shop-inventory) | [add-ons](http://addons.silverstripe.org/add-ons/milkyway-multimedia/ss-shop-inventory) | [packagist](https://packagist.org/packages/milkyway-multimedia/ss-shop-inventory)
milkyway-multimedia/ss-shop-checkout-extras | [github](http://www.github.com/milkyway-multimedia/ss-shop-checkout-extras) | [add-ons](http://addons.silverstripe.org/add-ons/milkyway-multimedia/ss-shop-checkout-extras) | [packagist](https://packagist.org/packages/milkyway-multimedia/ss-shop-checkout-extras)
markguinn/silverstripe-wishlist | [github](http://www.github.com/markguinn/silverstripe-wishlist) | [add-ons](http://addons.silverstripe.org/add-ons/markguinn/silverstripe-wishlist) | [packagist](https://packagist.org/packages/markguinn/silverstripe-wishlist)
burnbright/silverstripe-simple-shop | [github](http://www.github.com/burnbright/silverstripe-simple-shop) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-simple-shop) | [packagist](https://packagist.org/packages/burnbright/silverstripe-simple-shop)
burnbright/silverstripe-bootstrap-shop | [github](http://www.github.com/burnbright/silverstripe-bootstrap-shop) | [add-ons](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-bootstrap-shop) | [packagist](https://packagist.org/packages/burnbright/silverstripe-bootstrap-shop)

You could also search [addons](http://addons.silverstripe.org/add-ons?search=shop) or [packgist](https://packagist.org/search/?q=silverstripe%20shop) or [github](https://github.com/search?q=silverstripe+shop) for other submodules.

The code for the [shop demo site](http://demo.ss-shop.org/) is available here:

https://github.com/burnbright/silverstripe-shop-demo

