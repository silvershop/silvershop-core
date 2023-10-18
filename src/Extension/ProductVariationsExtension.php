<?php

namespace SilverShop\Extension;

use SilverShop\Forms\VariationForm;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\Variation;
use SilverShop\ORM\FieldType\ShopCurrency;
use SilverShop\Page\Product;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Adds extra fields and relationships to Products for variations support.
 *
 * @package    silvershop
 * @subpackage variations
 */
class ProductVariationsExtension extends DataExtension
{
    private static $has_many = [
        'Variations' => Variation::class,
    ];

    private static $many_many = [
        'VariationAttributeTypes' => AttributeType::class,
    ];

    /**
     * Adds variations specific fields to the CMS.
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Variations', [
            ListboxField::create(
                'VariationAttributeTypes',
                _t(__CLASS__ . '.Attributes', "Attributes"),
                AttributeType::get()->map('ID', 'Title')->toArray()
            )
                ->setDescription(_t(
                    __CLASS__ . '.AttributesDescription',
                    'These are fields to indicate the way(s) each variation varies. Once selected, they can be edited on each variation.'
                )),
            $variationsGridField = GridField::create(
                'Variations',
                _t(__CLASS__ . '.Variations', 'Variations'),
                $this->owner->Variations(),
                GridFieldConfig_RecordEditor::create(100)
            )
        ]);

        $variationsGridField->getConfig()->addComponent($sort = new GridFieldOrderableRows('Sort'));

        if ($this->owner->Variations()->exists()) {
            $fields->addFieldToTab(
                'Root.Pricing',
                LabelField::create(
                    'variationspriceinstructinos',
                    _t(
                        __CLASS__ . '.VariationsInfo',
                        'Price - Because you have one or more variations, the price can be set in the "Variations" tab.'
                    )
                )
            );
            $fields->removeFieldFromTab('Root.Pricing', 'BasePrice');
            $fields->removeFieldFromTab('Root.Main', 'InternalItemID');
        }
    }

    public function PriceRange()
    {
        $variations = $this->owner->Variations();

        if (!Product::config()->allow_zero_price) {
            $variations = $variations->filter('Price:GreaterThan', 0);
        }

        if (!$variations->exists() || !$variations->Count()) {
            return null;
        }

        $prices = $variations->map('ID', 'SellingPrice')->toArray();
        $pricedata = [
            'HasRange' => false,
            'Max' => ShopCurrency::create(),
            'Min' => ShopCurrency::create(),
            'Average' => ShopCurrency::create(),
        ];
        $count = count($prices);
        $sum = array_sum($prices);
        $maxprice = max($prices);
        $minprice = min($prices);
        $pricedata['HasRange'] = ($minprice != $maxprice);
        $pricedata['Max']->setValue($maxprice);
        $pricedata['Min']->setValue($minprice);
        if ($count > 0) {
            $pricedata['Average']->setValue($sum / $count);
        }

        return ArrayData::create($pricedata);
    }

    /**
     * Pass an array of attribute ids to query for the appropriate variation.
     *
     * @param array $attributes
     *
     * @return Variation|null
     */
    public function getVariationByAttributes(array $attributes)
    {
        if (!is_array($attributes)) {
            return null;
        }

        $attrs = array_filter(array_values($attributes));
        $set = Variation::get()->filter('ProductID', $this->owner->ID);

        foreach ($attrs as $i => $valueid) {
            $alias = "A$i";
            $set = $set->innerJoin(
                'SilverShop_Variation_AttributeValues',
                "\"SilverShop_Variation\".\"ID\" = \"$alias\".\"SilverShop_VariationID\"",
                $alias
            )->where(["\"$alias\".\"SilverShop_AttributeValueID\" = ?" => $valueid]);
        }

        return $set->first();
    }

    /**
     * Generates variations based on selected attributes.
     *
     * @param  AttributeType $attributetype
     * @param  array         $values
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function generateVariationsFromAttributes(AttributeType $attributetype, array $values)
    {
        //TODO: introduce transactions here, in case objects get half made etc
        //if product has variation attribute types
        if (!empty($values)) {
            //TODO: get values dataobject set
            $avalues = $attributetype->convertArrayToValues($values);
            $existingvariations = $this->owner->Variations();
            if ($existingvariations->exists()) {
                //delete old variation, and create new ones - to prevent modification of exising variations
                foreach ($existingvariations as $oldvariation) {
                    $oldvalues = $oldvariation->AttributeValues();
                    foreach ($avalues as $value) {
                        $newvariation = $oldvariation->duplicate();
                        $newvariation->InternalItemID = $this->owner->InternalItemID . '-' . $newvariation->ID;
                        $newvariation->AttributeValues()->addMany($oldvalues);
                        $newvariation->AttributeValues()->add($value);
                        $newvariation->write();
                        $existingvariations->add($newvariation);
                    }
                    $existingvariations->remove($oldvariation);
                    $oldvariation->AttributeValues()->removeAll();
                    $oldvariation->delete();
                    $oldvariation->destroy();
                    //TODO: check that old variations actually stick around, as they will be needed for past orders etc
                }
            } else {
                foreach ($avalues as $value) {
                    $variation = Variation::create();
                    $variation->ProductID = $this->owner->ID;
                    $variation->Price = $this->owner->BasePrice;
                    $variation->write();
                    $variation->InternalItemID = $this->owner->InternalItemID . '-' . $variation->ID;
                    $variation->AttributeValues()->add($value);
                    $variation->write();
                    $existingvariations->add($variation);
                }
            }
        }
    }

    /**
     * Get all the {@link ProductAttributeValue} for a given attribute type,
     * based on this product's variations.
     *
     * @return DataList
     */
    public function possibleValuesForAttributeType($type)
    {
        if (!is_numeric($type)) {
            $type = $type->ID;
        }

        if (!$type) {
            return null;
        }

        $list = AttributeValue::get()
            ->innerJoin(
                'SilverShop_Variation_AttributeValues',
                '"SilverShop_AttributeValue"."ID" = "SilverShop_Variation_AttributeValues"."SilverShop_AttributeValueID"'
            )->innerJoin(
                'SilverShop_Variation',
                '"SilverShop_Variation_AttributeValues"."SilverShop_VariationID" = "SilverShop_Variation"."ID"'
            )->where(
                "TypeID = $type AND \"SilverShop_Variation\".\"ProductID\" = " . $this->owner->ID
            );

        if (!Product::config()->allow_zero_price) {
            $list = $list->where('"SilverShop_Variation"."Price" > 0');
        }
        return $list;
    }

    /**
     * Make sure variations are deleted with product.
     */
    public function onAfterDelete()
    {
        $remove = false;
        // if a record is staged or live, leave it's variations alone.
        if (!property_exists($this, 'owner')) {
            $remove = true;
        } else {
            $staged = Versioned::get_by_stage($this->owner->ClassName, 'Stage')
                ->byID($this->owner->ID);
            $live = Versioned::get_by_stage($this->owner->ClassName, 'Live')
                ->byID($this->owner->ID);
            if (!$staged && !$live) {
                $remove = true;
            }
        }
        if ($remove) {
            foreach ($this->owner->Variations() as $variation) {
                $variation->delete();
                $variation->destroy();
            }
        }
    }

    public function updateFormClass(&$formClass)
    {
        if ($this->owner->Variations()->exists()) {
            $formClass = VariationForm::class;
        }
    }
}
