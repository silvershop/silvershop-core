<?php

namespace SilverShop\Tests;

use SilverShop\Admin\ProductCatalogAdmin;
use SilverShop\Cart\ShoppingCartController;
use SilverShop\Extension\ProductImageExtension;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Extension\SteppedCheckoutExtension;
use SilverShop\Forms\OrderActionsForm;
use SilverShop\Model\Address;
use SilverShop\Model\Modifiers\Shipping\Simple;
use SilverShop\Model\Modifiers\Tax\FlatTax;
use SilverShop\Model\Modifiers\Tax\GlobalTax;
use SilverShop\Model\Order;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\Variation;
use SilverShop\ORM\FieldType\I18nDatetime;
use SilverShop\ORM\FieldType\ShopCurrency;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
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
$cfg->set(ProductImageExtension::class, 'thumbnail_width', 140);
$cfg->set(ProductImageExtension::class, 'thumbnail_height', 100);
$cfg->set(ProductImageExtension::class, 'large_image_width', 200);
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
$cfg->set(ShopConfigExtension::class, 'email_from', null);
$cfg->set(ShopConfigExtension::class, 'base_currency', 'NZD');
$cfg->set(SteppedCheckoutExtension::class, 'first_step', null);
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
