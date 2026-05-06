<?php

declare(strict_types=1);

namespace SilverShop\Model\Product;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataObject;

/**
 * Stores a per-currency price for a product.
 *
 * When a product has prices defined for specific currencies, those prices
 * will be used directly instead of converting from the base price. This
 * gives shop owners precise control over pricing in each currency.
 *
 * @property string $Currency
 * @property float  $Price
 * @property int    $ProductID
 * @method   Product Product()
 */
class ProductCurrencyPrice extends DataObject
{
    private static string $table_name = 'SilverShop_ProductCurrencyPrice';

    private static string $singular_name = 'Product Currency Price';

    private static string $plural_name = 'Product Currency Prices';

    private static array $db = [
        'Currency' => 'Varchar(3)',
        'Price'    => 'Currency(19,4)',
    ];

    private static array $has_one = [
        'Product' => Product::class,
    ];

    private static array $summary_fields = [
        'Currency' => 'Currency',
        'Price'    => 'Price',
    ];

    private static array $indexes = [
        'CurrencyProductUnique' => [
            'type'    => 'unique',
            'columns' => ['Currency', 'ProductID'],
        ],
    ];

    public function canCreate($member = null, $context = []): bool
    {
        $product = $this->Product();
        return $product && $product->exists() ? $product->canEdit($member) : false;
    }

    public function canEdit($member = null): bool
    {
        $product = $this->Product();
        return $product && $product->exists() ? $product->canEdit($member) : false;
    }

    public function canDelete($member = null): bool
    {
        $product = $this->Product();
        return $product && $product->exists() ? $product->canEdit($member) : false;
    }

    public function canView($member = null): bool
    {
        $product = $this->Product();
        return $product && $product->exists() ? $product->canView($member) : false;
    }
}
