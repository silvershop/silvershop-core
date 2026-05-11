# SilverShop Module

The SilverShop module aims to provide developers with a framework for building, and customising ecommerce-based projects.
It includes facilities for customers to browse products and place orders, and for administrators to manage products and orders.
We've put a strong focus on testing. You can see the [![build status](https://github.com/silvershop/silvershop-core/actions/workflows/ci.yml/badge.svg)](https://github.com/silvershop/silvershop-core/actions/workflows/ci.yml) of this project, running on MySQL, SQLite, Postgres, as well as a few different versions of PHP.

[![Latest Stable Version](https://poser.pugx.org/silvershop/core/v/stable.png)](https://packagist.org/packages/silvershop/core)
[![Latest Unstable Version](https://poser.pugx.org/silvershop/core/v/unstable.png)](https://packagist.org/packages/silvershop/core)
[![CI](https://github.com/silvershop/silvershop-core/actions/workflows/ci.yml/badge.svg)](https://github.com/silvershop/silvershop-core/actions/workflows/ci.yml)
[![Code Coverage](https://scrutinizer-ci.com/g/silvershop/silvershop-core/badges/coverage.png?s=1abe84b468ef3d96646a0546954adba8131d6459)](https://scrutinizer-ci.com/g/silvershop/silvershop-core/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/silvershop/silvershop-core/badges/quality-score.png?s=d60753d6cca3817e80aca3dbb79eb5bd4140c981)](https://scrutinizer-ci.com/g/silvershop/silvershop-core/)
[![Total Downloads](https://poser.pugx.org/silvershop/core/downloads.png)](https://packagist.org/packages/silvershop/core)

* Website: http://www.silvershop.io
* Demo: https://demo.silvershop.io

Your contributions, and feedback are welcomed and appreciated. There are many ways you can [contribute to this project](https://github.com/silvershop/silvershop-core/wiki/Contributing).
A tremendous thanks to [everyone that has already contributed](https://github.com/silvershop/silvershop-core/graphs/contributors).


## Requirements

 * SilverStripe ^6 [framework](https://github.com/silverstripe/silverstripe-framework) & [cms](https://github.com/silverstripe/silverstripe-cms)
 * [Omnipay Module](https://github.com/burnbright/silverstripe-omnipay) + its dependencies.

See `composer.json` for exact set of dependencies.

* For a SilverStripe 4.x and 5.x compatible version, please use 3.0 or 3.1.
* For a SilverStripe 3.x compatible version, please use a 2.x release.

## Stay up to date / get in touch

* [Planning Trello Board](https://trello.com/b/85ZyINqI/silvershop-development-planning)
* [Roadmap](ROADMAP.md)
* Live chat on Gitter! [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/silvershop/silvershop-core?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
* [Twitter](https://twitter.com/silvershopcore)

## Documentation

 * https://github.com/silvershop/silvershop-core/blob/master/docs/en/index.md - for Developers
 * https://github.com/silvershop/silvershop-core/blob/master/docs_user/en/index.md - for Users

## Installation Instructions

To install SilverShop using [composer](http://doc.silverstripe.org/framework/en/installation/composer), run the following command:

```
composer require silvershop/core
```

### Build Tasks

There are a few useful tasks that can be run via a url to help you test:

 * `{yoursite.com}/dev/tasks/PopulateShopTask` - will create cart, checkout, account, category and product pages
 * `{yoursite.com}/dev/tasks/PopulateCartTask` - will add products to the cart, and navitate you to the checkout

## Configuration

You can view various configuration options in the 'example_config.yml' file.

### Frontend CSS (optional)

SilverShop Layout templates include bundled CSS under `client/dist/css/` (shared design tokens in `silvershop-base.css`, plus page-specific files such as `product.css`, `cart.css`, and `checkout.css`). To **disable** those styles and rely entirely on your theme or build pipeline, set:

```yaml
SilverShop\View\ShopFrontendAssetConfig:
  include_default_styles: false
```

When disabled, templates skip `<% require css(...) %>` for those storefront styles only. JavaScript for cart JSON controls, checkout behaviour (session keep-alive, address-book toggle, payment method panels, AJAX helpers), and CMS requirements are unchanged.

### Offsite payment gateways (e.g. PayPal Express) lose the session on return in CMS6

SilverStripe CMS6 changed the default session cookie SameSite attribute to Strict. This means
the browser will not send the session cookie when the user is redirected back from an external
payment provider, resulting in a new empty session and a broken checkout flow.

To fix this, set `cookie_samesite` to `None` or `'Lax'` in your project's session config:

```yaml
SilverStripe\Control\Session:
  cookie_samesite: None # Required for offsite payment gateways (PayPal Express etc) to preserve session on return
# or
  cookie_samesite: 'Lax' # Required for offsite payment gateways (PayPal Express etc) to preserve session on return
```

`SameSite=None` requires the cookie to be sent over HTTPS — SilverStripe enforces the Secure flag
automatically when this setting is used, so no additional config is needed. This was not an
issue in CMS5, which did not default to Strict.

## Core Features

 * Product Catalog - Products extend Page, and can be browsed within Product Category pages.
 * Cart Page - For viewing and updating your cart.
 * Checkout - Gather delivery/billing details and anything specific to the order. Can be single-page or multi-step.
 * Online Payments - Via the omnipay module.
 * Administration - Manage the catalog and orders in the CMS.

Futher functionality is provided by add-on submodules.

## Add-on Sub Modules

Don't reinvent the wheel! Get additional pre-built functionality with sub modules. All additional functional will be tagged on [packgist](https://packagist.org/search/?q=silvershop) as #silvershop
