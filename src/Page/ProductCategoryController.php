<?php

namespace SilverShop\Page;

use PageController;
use SilverShop\ListSorter\ListSorter;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;

class ProductCategoryController extends PageController
{
    /**
     * Return the products for this group.
     */
    public function Products($recursive = true): PaginatedList
    {
        $products = $this->ProductsShowable($recursive);

        //sort the products
        $products = $this->getSorter()->sortList($products);

        //paginate the products, if necessary
        $pagelength = ProductCategory::config()->page_length;
        if ($pagelength > 0) {
            $products = PaginatedList::create($products, $this->request);
            $products->setPageLength($pagelength);
            $products->TotalCount = $products->getTotalItems();
        }

        return $products;
    }

    /**
     * Return products that are featured, that is products that have "FeaturedProduct = 1"
     */
    public function FeaturedProducts($recursive = true): DataList
    {
        return $this->ProductsShowable($recursive)
            ->filter('Featured', true);
    }

    /**
     * Return products that are not featured, that is products that have "FeaturedProduct = 0"
     */
    public function NonFeaturedProducts($recursive = true): DataList
    {
        return $this->ProductsShowable($recursive)
            ->filter('Featured', false);
    }

    /**
     * Sorting controls
     */
    public function getSorter(): ListSorter
    {
        $options = [];
        foreach (ProductCategory::config()->sort_options as $k => $v) {
            // make the label translatable
            $k = _t(ProductCategory::class . '.' . $k, $k);
            $options[$k] = $v;
        }

        $listSorter = ListSorter::create($this->request, $options);
        $this->extend('updateSorter', $listSorter);

        return $listSorter;
    }
}
