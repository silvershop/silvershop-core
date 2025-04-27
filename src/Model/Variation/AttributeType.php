<?php

namespace SilverShop\Model\Variation;

use SilverShop\Page\Product;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;

/**
 * Producte Attribute Type
 * Types of product attributes.
 * eg: color, size, length
 *
 * @property ?string $Name
 * @property ?string $Label
 * @method HasManyList<AttributeValue> Values()
 * @method ManyManyList<Product> Product()
 */
class AttributeType extends DataObject
{
    private static array $db = [
        'Name' => 'Varchar', //for back-end use
        'Label' => 'Varchar' //for front-end use
    ];

    private static array $has_many = [
        'Values' => AttributeValue::class,
    ];

    private static array $belongs_many_many = [
        'Product' => Product::class,
    ];

    private static array $summary_fields = [
        'Name' => 'Name',
        'Label' => 'Label',
    ];

    private static array $indexes = [
        'LastEdited' => true,
    ];

    private static string $default_sort = 'ID ASC';

    private static string $singular_name = 'Attribute';

    private static string $plural_name = 'Attributes';

    private static string $table_name = 'SilverShop_AttributeType';

    public static function find_or_make($name): AttributeType
    {
        if ($type = AttributeType::get()->filter('Name:nocase', $name)->first()
        ) {
            return $type;
        }
        $type = AttributeType::create();
        $type->Name = $name;
        $type->Label = $name;
        $type->write();

        return $type;
    }

    public function getCMSFields(): FieldList
    {
        $fieldList = FieldList::create(
            TextField::create('Name', $this->fieldLabel('Name')),
            TextField::create('Label', $this->fieldLabel('Label'))
        );
        if ($this->isInDB()) {
            $fieldList->push(
                GridField::create(
                    'Values',
                    $this->fieldLabel('Values'),
                    $this->Values(),
                    GridFieldConfig_RecordEditor::create()
                )
            );
        } else {
            $fieldList->push(
                LiteralField::create(
                    'Values',
                    '<p class="message warning">' .
                    _t(__CLASS__ . '.SaveFirstInfo', 'Save first, then you can add values.') .
                    '</p>'
                )
            );
        }

        $this->extend('updateCMSFields', $fieldList);

        return $fieldList;
    }

    public function addValues(array $values): void
    {
        $arrayList = $this->convertArrayToValues($values);
        $this->Values()->addMany($arrayList);
    }

    /**
     * Finds or creates values for this type.
     *
     */
    public function convertArrayToValues(array $values): ArrayList
    {
        $arrayList = ArrayList::create();
        foreach ($values as $value) {
            $val = $this->Values()->find('Value', $value);
            if (!$val) {  //TODO: ignore case, if possible
                $val = AttributeValue::create();
                $val->Value = $value;
                $val->TypeID = $this->ID;
                $val->write();
            }
            $arrayList->push($val);
        }

        return $arrayList;
    }

    /**
     * Returns a dropdown field for the user to select a variant.
     *
     * @param string    $emptystring
     * @param ArrayList $values
     */
    public function getDropDownField($emptystring = null, $values = null): ?DropdownField
    {
        $values = ($values) ? $values : $this->Values()->sort(['Sort' => 'ASC', 'Value' => 'ASC']);

        if ($values->exists()) {
            $field = DropdownField::create(
                'ProductAttributes[' . $this->ID . ']',
                $this->Label,
                $values->map('ID', 'Value')
            );

            if ($emptystring) {
                $field->setEmptyString($emptystring);
            }

            return $field;
        }

        return null;
    }

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        if ($this->Name && !$this->Label) {
            $this->Label = $this->Name;
        } elseif ($this->Label && !$this->Name) {
            $this->Name = $this->Label;
        }
    }

    public function canDelete($member = null): bool
    {
        //TODO: prevent deleting if has been used
        return true;
    }
}
