<?php

declare(strict_types=1);

namespace SilverShop\Model;

use SilverShop\Page\Product;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * A tax class groups products that share the same tax rate.
 *
 * @property string $Title
 * @property float $Rate
 *
 * @method HasManyList<Product> Products()
 */
class TaxClass extends DataObject
{
    private static string $table_name = 'SilverShop_TaxClass';

    private static string $singular_name = 'Tax Class';

    private static string $plural_name = 'Tax Classes';

    private static array $db = [
        'Title' => 'Varchar(100)',
        'Rate' => 'Double',
    ];

    private static array $defaults = [
        'Rate' => 0,
    ];

    private static array $has_many = [
        'Products' => Product::class,
    ];

    private static array $summary_fields = [
        'Title' => 'Title',
        'Rate' => 'Rate',
    ];

    public function validate(): ValidationResult
    {
        $result = parent::validate();
        if ($this->Rate === null || $this->Rate === '') {
            $result->addError(_t(__CLASS__ . '.RateRequired', 'Tax class rate is required.'));
            return $result;
        }

        if ($this->Rate < 0) {
            $result->addError(_t(__CLASS__ . '.RateNonNegative', 'Tax class rate must not be negative.'));
        }

        return $result;
    }
}
