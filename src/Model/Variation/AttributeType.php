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
 * @property string $Name
 * @property string $Label
 * @method   AttributeValue[]|HasManyList Values()
 * @method   Product[]|ManyManyList Product()
 */
class AttributeType extends DataObject
{
    private static $db = [
        'Name' => 'Varchar', //for back-end use
        'Label' => 'Varchar' //for front-end use
    ];

    private static $has_many = [
        'Values' => AttributeValue::class,
    ];

    private static $belongs_many_many = [
        'Product' => Product::class,
    ];

    private static $summary_fields = [
        'Name' => 'Name',
        'Label' => 'Label',
    ];

    private static $indexes = [
        'LastEdited' => true,
    ];

    private static $default_sort = 'ID ASC';

    private static $singular_name = 'Attribute';

    private static $plural_name = 'Attributes';

    private static $table_name = 'SilverShop_AttributeType';

    public static function find_or_make($name)
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

    public function getCMSFields()
    {
        $fields = FieldList::create(
            TextField::create('Name', $this->fieldLabel('Name')),
            TextField::create('Label', $this->fieldLabel('Label'))
        );
        if ($this->isInDB()) {
            $fields->push(
                GridField::create(
                    'Values',
                    $this->fieldLabel('Values'),
                    $this->Values(),
                    GridFieldConfig_RecordEditor::create()
                )
            );
        } else {
            $fields->push(
                LiteralField::create(
                    'Values',
                    '<p class="message warning">' .
                    _t(__CLASS__ . '.SaveFirstInfo', 'Save first, then you can add values.') .
                    '</p>'
                )
            );
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function addValues(array $values)
    {
        $avalues = $this->convertArrayToValues($values);
        $this->Values()->addMany($avalues);
    }

    /**
     * Finds or creates values for this type.
     *
     * @param array $values
     *
     * @return ArrayList
     */
    public function convertArrayToValues(array $values)
    {
        $set = ArrayList::create();
        foreach ($values as $value) {
            $val = $this->Values()->find('Value', $value);
            if (!$val) {  //TODO: ignore case, if possible
                $val = AttributeValue::create();
                $val->Value = $value;
                $val->TypeID = $this->ID;
                $val->write();
            }
            $set->push($val);
        }

        return $set;
    }

    /**
     * Returns a dropdown field for the user to select a variant.
     *
     * @param string    $emptyString
     * @param ArrayList $values
     *
     * @return DropdownField
     */
    public function getDropDownField($emptystring = null, $values = null)
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

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->Name && !$this->Label) {
            $this->Label = $this->Name;
        } elseif ($this->Label && !$this->Name) {
            $this->Name = $this->Label;
        }
    }

    public function canDelete($member = null)
    {
        //TODO: prevent deleting if has been used
        return true;
    }
}
