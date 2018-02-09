<?php

namespace SilverShop\Page;

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
 */
class ProductCategory extends Page implements i18nEntityProvider
{
    private static $belongs_many_many = [
        'Products' => Product::class,
    ];

    private static $singular_name = 'Category';

    private static $plural_name = 'Categories';

    private static $icon = 'silvershop/core: client/dist/images/icons/folder.gif';

    private static $table_name = 'SilverShop_ProductCategory';

    private static $default_child = 'Product';

    private static $include_child_groups = true;

    private static $page_length = 12;

    private static $must_have_price = true;

    private static $sort_options = [
        'Alphabetical' => 'URLSegment',
        'Price' => 'BasePrice',
    ];

    /**
     * Retrieve a set of products, based on the given parameters. Checks get query for sorting and pagination.
     *
     * @param bool $recursive include sub-categories
     *
     * @return DataList
     */
    public function ProductsShowable($recursive = true)
    {
        // Figure out the categories to check
        $groupids = array($this->ID);
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
    public function AllChildCategoryIDs()
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
     *
     * @return DataList
     */
    public function ChildCategories($recursive = false)
    {
        $ids = array($this->ID);
        if ($recursive) {
            $ids += $this->AllChildCategoryIDs();
        }

        return ProductCategory::get()->filter('ParentID', $ids);
    }

    /**
     * Recursively generate a product menu, starting from the topmost category.
     *
     * @return DataList
     */
    public function GroupsMenu()
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
    public function NestedTitle($level = 10, $separator = ' > ', $field = 'MenuTitle')
    {
        $item = $this;
        while ($item && $level > 0) {
            $parts[] = $item->{$field};
            $item = $item->Parent;
            $level--;
        }
        return implode($separator, array_reverse($parts));
    }

    public function provideI18nEntities()
    {
        $entities = parent::provideI18nEntities();

        // add the sort option keys
        foreach ($this->config()->sort_options as $key => $value) {
            $entities[__CLASS__ . '.' . $key] = array(
                $key,
                "Sort by the '$value' field",
            );
        }

        return $entities;
    }
}
