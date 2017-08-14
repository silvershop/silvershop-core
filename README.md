# SilverShop Module

The SilverShop module aims to provide developers with a framework for building, and customising ecommerce-based projects.
It includes facilities for customers to browse products and place orders, and for administrators to manage products and orders.
We've put a strong focus on testing, and thanks to TravisCI, you can see the [build status](https://travis-ci.org/silvershop/silvershop-core) of this project, running on MySQL, SQLite, Postgres, as well as a few different versions of PHP.

[![Latest Stable Version](https://poser.pugx.org/silvershop/core/v/stable.png)](https://packagist.org/packages/silvershop/core)
[![Latest Unstable Version](https://poser.pugx.org/silvershop/core/v/unstable.png)](https://packagist.org/packages/silvershop/core)
[![Build Status](https://travis-ci.org/silvershop/silvershop-core.svg?branch=master)](http://travis-ci.org/silvershop/silvershop-core)
[![Code Coverage](https://scrutinizer-ci.com/g/silvershop/silvershop-core/badges/coverage.png?s=1abe84b468ef3d96646a0546954adba8131d6459)](https://scrutinizer-ci.com/g/silvershop/silvershop-core/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/silvershop/silvershop-core/badges/quality-score.png?s=d60753d6cca3817e80aca3dbb79eb5bd4140c981)](https://scrutinizer-ci.com/g/silvershop/silvershop-core/)
[![Total Downloads](https://poser.pugx.org/silvershop/core/downloads.png)](https://packagist.org/packages/silvershop/core)

* Website: http://silvershop.github.io
* Demo: http://demo.ss-shop.org

Your contributions, and feedback are welcomed and appreciated. There are many ways you can [contribute to this project](https://github.com/silvershop/silvershop-core/wiki/Contributing).
A tremendous thanks to [everyone that has already contributed](https://github.com/silvershop/silvershop-core/graphs/contributors).


## Stay up to date / get in touch

* [Planning Trello Board](https://trello.com/b/85ZyINqI/silvershop-development-planning)
* [Roadmap](ROADMAP.md)
* Live chat on Gitter! [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/silvershop/silvershop-core?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
* [Twitter](https://twitter.com/silvershopcore)

## Requirements

 * SilverStripe 3.1 or higher [framework](https://github.com/silverstripe/silverstripe-framework) & [cms](https://github.com/silverstripe/silverstripe-cms)
 * [Omnipay Module](https://github.com/burnbright/silverstripe-omnipay) + its dependencies.

See `composer.json` for exact set of dependencies.

## Documentation

 * https://github.com/silvershop/silvershop-core/blob/master/docs/en/index.md - for Developers
 * https://github.com/silvershop/silvershop-core/blob/master/docs_user/en/index.md - for Users

## Installation Instructions

To install silverstripe + shop into a directory called 'myshop', using [composer](http://doc.silverstripe.org/framework/en/installation/composer), run the following commands:
```
composer create-project silverstripe/installer myshop
composer require -d myshop "silvershop/core"
```

### Build Tasks

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

Don't reinvent the wheel! Get additional pre-built functionality with sub modules. All additional functional will be tagged on [packgist](https://packagist.org/search/?q=silverstripe%20shop) or [github](https://github.com/search?q=silverstripe+shop).

The code for the [shop demo site](http://demo.ss-shop.org/) is available here:

https://github.com/burnbright/silverstripe-shop-demo

