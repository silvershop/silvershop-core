<?php

namespace SilverShop\Admin;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Admin\ModelAdmin;

/**
 * Product Catalog Admin
 **/
class ProductCatalogAdmin extends ModelAdmin
{
    private static $url_segment = 'catalog';

    private static $menu_title = 'Catalog';

    private static $menu_priority = 5;

    private static $menu_icon_class = 'silvershop-icon-catalog';

    private static $managed_models = [
        Product::class,
        ProductCategory::class,
        AttributeType::class,
    ];

    private static $model_importers = [
        Product::class => ProductBulkLoader::class,
    ];
}
