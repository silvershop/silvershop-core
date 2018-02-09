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
 * @property string $Value
 * @property int $Sort
 * @method   AttributeType Type()
 * @method   Variation[]|ManyManyList ProductVariation()
 */
class AttributeValue extends DataObject
{
    private static $db = [
        'Value' => 'Varchar',
        'Sort' => 'Int',
    ];

    private static $has_one = [
        'Type' => AttributeType::class,
    ];

    private static $belongs_many_many = [
        'ProductVariation' => Variation::class,
    ];

    private static $summary_fields = [
        'Value' => 'Value',
    ];

    private static $indexes = [
        'LastEdited' => true,
        'Sort' => true,
    ];

    private static $table_name = 'SilverShop_AttributeValue';

    private static $default_sort = '"TypeID" ASC, "Sort" ASC, "Value" ASC';

    private static $singular_name = 'Value';

    private static $plural_name = 'Values';

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function (FieldList $fields) {

                $fields->removeByName('TypeID');
                $fields->removeByName('Sort');
            }
        );

        return parent::getCMSFields();
    }
}
