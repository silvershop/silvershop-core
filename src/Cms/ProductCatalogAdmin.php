<?php

namespace SilverShop\Core\Cms;


use SilverShop\Core\Product\Product;
use SilverShop\Core\Product\ProductCategory;
use SilverShop\Core\Product\Variation\AttributeType;
use SilverStripe\Admin\ModelAdmin;


/**
 * Product Catalog Admin
 *
 * @package    shop
 * @subpackage cms
 **/
class ProductCatalogAdmin extends ModelAdmin
{
    private static $url_segment = 'catalog';

    private static $menu_title = 'Catalog';

    private static $menu_priority = 5;

    private static $menu_icon = 'silvershop/images/icons/catalog-admin.png';

    private static $managed_models = [
        Product::class,
        ProductCategory::class,
        AttributeType::class,
    ];

    private static $model_importers = [
        Product::class => ProductBulkLoader::class,
    ];
}
