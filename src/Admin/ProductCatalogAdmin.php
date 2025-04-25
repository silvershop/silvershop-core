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
    private static string $url_segment = 'catalog';

    private static string $menu_title = 'Catalog';

    private static int $menu_priority = 5;

    private static string $menu_icon_class = 'silvershop-icon-catalog';

    private static array $managed_models = [
        Product::class,
        ProductCategory::class,
        AttributeType::class,
    ];

    private static array $model_importers = [
        Product::class => ProductBulkLoader::class,
    ];
}
