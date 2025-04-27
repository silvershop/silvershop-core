<?php

namespace SilverShop\Model\Variation;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Product Attribute Value
 * The actual values for a type of product attribute.
 * eg: red, green, blue... 12, 13, 15
 *
 * @property ?string $Value
 * @property int $Sort
 * @method   AttributeType Type()
 * @method ManyManyList<Variation> ProductVariation()
 * @property int $TypeID
 */
class AttributeValue extends DataObject
{
    private static array $db = [
        'Value' => 'Varchar',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Type' => AttributeType::class,
    ];

    private static array $belongs_many_many = [
        'ProductVariation' => Variation::class,
    ];

    private static array $summary_fields = [
        'Value' => 'Value',
    ];

    private static array $indexes = [
        'LastEdited' => true,
        'Sort' => true,
    ];

    private static string $table_name = 'SilverShop_AttributeValue';

    private static string $default_sort = '"TypeID" ASC, "Sort" ASC, "Value" ASC';

    private static string $singular_name = 'Value';

    private static string $plural_name = 'Values';

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(
            function (FieldList $fields): void {

                $fields->removeByName('TypeID');
                $fields->removeByName('Sort');
            }
        );

        return parent::getCMSFields();
    }
}
