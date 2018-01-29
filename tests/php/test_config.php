<?php

namespace SilverShop\Core\Tests;


use SilverShop\Core\Account\OrderActionsForm;
use SilverShop\Core\Cart\ShoppingCartController;
use SilverShop\Core\Checkout\Step\SteppedCheckout;
use SilverShop\Core\Cms\ProductCatalogAdmin;
use SilverShop\Core\Cms\ShopConfig;
use SilverShop\Core\Model\Address;
use SilverShop\Core\Model\FieldType\I18nDatetime;
use SilverShop\Core\Model\FieldType\ShopCurrency;
use SilverShop\Core\Model\Order;
use SilverShop\Core\Modifiers\Shipping\Simple;
use SilverShop\Core\Modifiers\Tax\FlatTax;
use SilverShop\Core\Modifiers\Tax\GlobalTax;
use SilverShop\Core\Product\Product;
use SilverShop\Core\Product\ProductCategory;
use SilverShop\Core\Product\ProductImage;
use SilverShop\Core\Product\Variation\AttributeType;
use SilverShop\Core\Product\Variation\Variation;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;


/// Reset to all default configuration settings.

$cfg = Config::modify();

$cfg->set(Injector::class, DBDatetime::class, array("class" => I18nDatetime::class));

//remove array configs (these get merged, rater than replaced)

$cfg->remove(Payment::class, "allowed_gateways");
$cfg->remove(Order::class, "modifiers");
$cfg->remove(ProductCatalogAdmin::class, "managed_models");
$cfg->remove(ProductCategory::class, "sort_options");

// non-ecommerce
$cfg->set(Member::class, 'unique_identifier_field', 'Email');
$cfg->set(Email::class, 'admin_email', 'shopadmin@example.com');
$cfg->set(
    Payment::class,
    'allowed_gateways',
    [
        'Dummy',
        'Manual',
    ]
);

// i18n
$cfg->set(ShopCurrency::class, 'decimal_delimiter', '.');
$cfg->set(ShopCurrency::class, 'thousand_delimiter', '');
$cfg->set(ShopCurrency::class, 'negative_value_format', '-%s');

// products
$cfg->set(Product::class, 'global_allow_purchase', true);
$cfg->set(
    ProductCatalogAdmin::class,
    'managed_models',
    [
        Product::class,
        ProductCategory::class,
        Variation::class,
        AttributeType::class
    ]
);
$cfg->set(ProductImage::class, 'thumbnail_width', 140);
$cfg->set(ProductImage::class, 'thumbnail_height', 100);
$cfg->set(ProductImage::class, 'large_image_width', 200);
$cfg->set(ProductCategory::class, 'include_child_groups', true);
$cfg->set(ProductCategory::class, 'page_length', 10);
$cfg->set(ProductCategory::class, 'must_have_price', true);
$cfg->set(ProductCategory::class, 'sort_options', array('Title' => 'Alphabetical', 'Price' => 'Lowest Price'));

// cart, order
$cfg->set(Order::class, 'modifiers', array());
$cfg->set(Order::class, 'cancel_before_payment', true);
$cfg->set(Order::class, 'cancel_before_processing', false);
$cfg->set(Order::class, 'cancel_before_sending', false);
$cfg->set(Order::class, 'cancel_after_sending', false);
$cfg->set(ShoppingCartController::class, 'direct_to_cart_page', false);

//modifiers
$cfg->set(FlatTax::class, 'name', 'NZD');
$cfg->set(FlatTax::class, 'rate', 0.15);
$cfg->set(FlatTax::class, 'exclusive', true);

$cfg->set(
    GlobalTax::class,
    'country_rates',
    array(
        "NZ" => array("rate" => 0.15, "name" => "GST", "exclusive" => false),
    )
);

$cfg->set(Simple::class, 'default_charge', 10);
$cfg->set(Simple::class, 'charges_for_countries', array('US' => 10, 'NZ' => 5));

// checkout
$cfg->set(ShopConfig::class, 'email_from', null);
$cfg->set(ShopConfig::class, 'base_currency', 'NZD');
$cfg->set(SteppedCheckout::class, 'first_step', null);
$cfg->set(
    Address::class,
    'requiredfields',
    [
        'Address',
        'City',
        'State',
        'Country',
    ]
);
$cfg->set(OrderActionsForm::class, 'set_allow_cancelling', false);
$cfg->set(OrderActionsForm::class, 'set_allow_paying', false);
