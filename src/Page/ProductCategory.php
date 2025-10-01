<?php

namespace SilverShop\Page;

use SilverStripe\ORM\ManyManyList;
use Page;
use SilverShop\Extension\ProductVariationsExtension;
use SilverStripe\i18n\i18nEntityProvider;
use SilverStripe\ORM\DataList;

/**
 * Product Category provides a way to hierartically categorise products.
 *
 * It contains functions for versioning child products
 *
 * @package shop
 * @method ManyManyList<Product> Products()
 */
class ProductCategory extends Page implements i18nEntityProvider
{
    private static array $belongs_many_many = [
        'Products' => Product::class,
    ];

    private static string $singular_name = 'Category';

    private static string $plural_name = 'Categories';

    private static $icon_class = 'font-icon-p-archive';

    private static string $table_name = 'SilverShop_ProductCategory';

    private static string $default_child = 'Product';

    private static bool $include_child_groups = true;

    private static int $page_length = 12;

    private static bool $must_have_price = true;

    private static array $sort_options = [
        'Alphabetical' => 'URLSegment',
        'Price' => 'BasePrice',
    ];

    /**
     * Retrieve a set of products, based on the given parameters. Checks get query for sorting and pagination.
     *
     * @param bool $recursive include sub-categories
     */
    public function ProductsShowable($recursive = true): DataList
    {
        // Figure out the categories to check
        $groupids = [$this->ID];
        if (!empty($recursive) && self::config()->include_child_groups) {
            $groupids += $this->AllChildCategoryIDs();
        }
        $products = Product::get()->filterAny(
            [
            'ParentID' => $groupids,
            'ProductCategories.ID' => $groupids
            ]
        );
        if (self::config()->must_have_price) {
            if (Product::has_extension(ProductVariationsExtension::class)) {
                $products = $products->filterAny(
                    [
                    'BasePrice:GreaterThan' => 0,
                    'Variations.Price:GreaterThan' => 0
                    ]
                );
            } else {
                $products = $products->filter('BasePrice:GreaterThan', 0);
            }
        }

        $this->extend('updateProductsShowable', $products);

        return $products;
    }

    /**
     * Loop down each level of children to get all ids.
     */
    public function AllChildCategoryIDs(): array
    {
        $ids = [$this->ID];
        $allids = [];
        do {
            $ids = ProductCategory::get()
                ->filter('ParentID', $ids)
                ->getIDList();
            $allids += $ids;
        } while (!empty($ids));

        return $allids;
    }

    /**
     * Return children ProductCategory pages of this category.
     *
     * @param bool $recursive
     */
    public function ChildCategories($recursive = false): DataList
    {
        $ids = [$this->ID];
        if ($recursive) {
            $ids += $this->AllChildCategoryIDs();
        }

        return ProductCategory::get()->filter('ParentID', $ids);
    }

    /**
     * Recursively generate a product menu, starting from the topmost category.
     */
    public function GroupsMenu(): DataList
    {
        if ($this->Parent() instanceof ProductCategory) {
            return $this->Parent()->GroupsMenu();
        }
        return ProductCategory::get()
            ->filter('ParentID', $this->ID);
    }

    /**
     * Override the nested title defaults, to show deeper nesting in the CMS.
     *
     * @param integer $level     nesting level
     * @param string  $separator seperate nesting with this string
     */
    public function NestedTitle($level = 10, $separator = ' > ', $field = 'MenuTitle'): string
    {
        $item = $this;
        $parts = [];
        while ($item && $level > 0) {
            $parts[] = $item->{$field};
            $item = $item->Parent;
            $level--;
        }
        return implode($separator, array_reverse($parts));
    }

    public function provideI18nEntities(): array
    {
        $entities = parent::provideI18nEntities();

        // add the sort option keys
        foreach ($this->config()->sort_options as $key => $value) {
            $entities[__CLASS__ . '.' . $key] = [
                $key,
                "Sort by the '$value' field",
            ];
        }

        return $entities;
    }
}
