<?php

declare(strict_types=1);

namespace SilverShop\Extension;

use SilverStripe\Model\ArrayData;
use SilverStripe\Core\Validation\ValidationException;
use SilverShop\Forms\VariationForm;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\Variation;
use SilverShop\ORM\FieldType\ShopCurrency;
use SilverShop\Page\Product;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Versioned\GridFieldArchiveAction;
use SilverShop\Forms\GridField\GridFieldGenerateVariationsButton;
use SilverShop\Forms\GridField\GridFieldVariationAttributeColumns;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Adds extra fields and relationships to Products for variations support.
 *
 * @package silvershop
 * @subpackage variations
 * @method HasManyList<Variation> Variations()
 * @method ManyManyList<AttributeType> VariationAttributeTypes()
 * @extends Extension<(Product & static)>
 */
class ProductVariationsExtension extends Extension
{
    private static array $has_many = [
        'Variations' => Variation::class,
    ];

    private static array $many_many = [
        'VariationAttributeTypes' => AttributeType::class,
    ];

    private static array $scaffold_cms_fields_settings = [
        'ignoreRelations' => [
            'VariationAttributeTypes',
            // Product::getCMSFields() adds the per-currency "Prices" grid to Root.Pricing
            // explicitly, so skip auto-scaffolding it (which leaves an empty "Prices" tab).
            'Prices',
        ]
    ];

    private static array $cascade_deletes = [
        'Variations',
    ];

    private static array $cascade_duplicates = [
        'Variations',
    ];

    /**
     * Adds variations specific fields to the CMS.
     */
    public function updateCMSFields(FieldList $fields): void
    {
        // Matrix editor: start from the record editor, then swap the static data columns
        // for inline-editable columns (Code + Price edited directly in the grid; the
        // read-only "Variation" column shows the attribute combination). Image, weight,
        // dimensions and stock remain on each row's edit form. A one-click, idempotent,
        // non-destructive "Generate variations" button fills in the missing combinations.
        $variationsConfig = GridFieldConfig_RecordEditor::create(100);
        // Rebuild the columns so the inline-editable data columns come first and the row
        // actions ("...") sit at the right; also prefer a real Delete over the versioned
        // Archive action.
        $variationsConfig->removeComponentsByType(GridFieldDataColumns::class);
        $variationsConfig->removeComponentsByType(GridFieldEditButton::class);
        $variationsConfig->removeComponentsByType(GridFieldDeleteAction::class);
        $variationsConfig->removeComponentsByType(GridFieldArchiveAction::class);
        $variationsConfig->removeComponentsByType(GridField_ActionMenu::class);

        $variationsConfig->addComponent($editableColumns = GridFieldEditableColumns::create());
        // One inline dropdown column per attribute type (Size, Colour, …), added right after
        // the "Variation" combination column. Renders nothing when the product has no attributes.
        $variationsConfig->addComponent(new GridFieldVariationAttributeColumns());
        $variationsConfig->addComponent(GridFieldOrderableRows::create('Sort'));
        $variationsConfig->addComponent(new GridFieldGenerateVariationsButton());
        $variationsConfig->addComponent(new GridFieldEditButton());
        $variationsConfig->addComponent(new GridFieldDeleteAction());
        $variationsConfig->addComponent(new GridField_ActionMenu());

        // Array form: 'title' labels the column header, 'callback' supplies the inline field.
        // The attribute dropdown columns (added below) identify each row, so no separate
        // read-only "Variation" combination column is needed.
        $displayFields = [
            'InternalItemID' => [
                'title' => _t(__CLASS__ . '.CodeColumn', 'Code'),
                'callback' => fn ($record, $column, $grid): TextField =>
                    TextField::create($column)->setAttribute('style', 'width:12em'),
            ],
            'Price' => [
                'title' => _t(__CLASS__ . '.PriceColumn', 'Price'),
                'callback' => fn ($record, $column, $grid): NumericField =>
                    NumericField::create($column)->setScale(2)->setAttribute('style', 'width:7em'),
            ],
        ];

        // Let optional modules contribute extra inline-editable columns keyed to a
        // getter/setter on Variation — e.g. silvershop/stock adds a per-variation
        // "Stock" column. Nothing is added when no such module is installed.
        $this->getOwner()->extend('updateVariationEditableColumns', $displayFields);

        $editableColumns->setDisplayFields($displayFields);

        $fields->addFieldsToTab('Root.Variations', [
            ListboxField::create(
                'VariationAttributeTypes',
                _t(__CLASS__ . '.Attributes', "Attributes"),
                AttributeType::get()->map('ID', 'Title')->toArray()
            )
                ->setDescription(_t(
                    __CLASS__ . '.AttributesDescription',
                    'Attributes are the ways this product varies (e.g. Size, Colour). Choose them and Save, then '
                    . 'use "Generate variations" to create the sellable combinations below.'
                )),
            // Shrink the fixed-width data columns to their content so the attribute dropdown
            // columns take the remaining width (the data cells otherwise stretch to fill the
            // 100%-wide grid table). Scoped to this editable grid via .ss-gridfield-editable.
            LiteralField::create(
                'variationsgridcss',
                '<style>'
                . '.ss-gridfield-editable .col-InternalItemID,'
                . '.ss-gridfield-editable .col-Price,'
                . '.ss-gridfield-editable .col-StockLevel,'
                . '.ss-gridfield-editable .col-StockUnlimited{width:1%;white-space:nowrap}'
                . '</style>'
            ),
            GridField::create(
                'Variations',
                _t(__CLASS__ . '.Variations', 'Variations'),
                $this->getOwner()->Variations(),
                $variationsConfig
            ),
            LiteralField::create(
                'variationsgridinfo',
                '<p class="message notice" style="display:flex;align-items:flex-start;gap:.5em">'
                . '<span class="font-icon-info-circled" aria-hidden="true"></span><span>' . _t(
                    __CLASS__ . '.VariationsGridInfo',
                    'Each row is a sellable variation with its own code, price and stock. Change what a variation is '
                    . 'with the attribute dropdowns, drag to reorder, and set an image via a row\'s edit button. '
                    . 'Click "Generate variations" to add any missing combinations. Changes are saved with the product.'
                ) . '</span></p>'
            ),
        ]);

        if ($this->getOwner()->Variations()->exists()) {
            $fields->addFieldToTab(
                'Root.Pricing',
                LiteralField::create(
                    'variationspriceinfo',
                    '<p class="message notice" style="margin-top:1.5em;display:flex;align-items:flex-start;gap:.5em">'
                    . '<span class="font-icon-info-circled" aria-hidden="true"></span><span>' . _t(
                        __CLASS__ . '.VariationsInfo',
                        'Because this product has one or more variations, the price is set per variation '
                        . 'on the "Variations" tab.'
                    ) . '</span></p>'
                )
            );
            $fields->removeFieldFromTab('Root.Pricing', 'BasePrice');
            $fields->removeFieldFromTab('Root.Main', 'InternalItemID');
        }
    }

    /**
     * Generate the full variation matrix for this product's selected attribute types —
     * the cartesian product of each type's values. Idempotent and non-destructive:
     * combinations that already exist are left untouched, only the missing ones are
     * created (priced at the product's BasePrice by default). Returns the number created.
     */
    public function generateVariations(): int
    {
        $product = $this->getOwner();
        $types = $product->VariationAttributeTypes();

        if (!$types->exists()) {
            return 0;
        }

        // Collect the value ids for each axis; a type with no values can't complete the matrix.
        $valueSets = [];
        foreach ($types as $type) {
            $ids = $type->Values()->column('ID');
            if ($ids === []) {
                return 0;
            }
            $valueSets[] = $ids;
        }

        // Cartesian product of the axis value ids.
        $combinations = [[]];
        foreach ($valueSets as $ids) {
            $expanded = [];
            foreach ($combinations as $combination) {
                foreach ($ids as $id) {
                    $expanded[] = array_merge($combination, [$id]);
                }
            }
            $combinations = $expanded;
        }

        // Signatures of the combinations that already exist.
        $existing = [];
        foreach ($product->Variations() as $variation) {
            $ids = $variation->AttributeValues()->column('ID');
            sort($ids);
            $existing[implode('-', $ids)] = true;
        }

        $created = 0;
        foreach ($combinations as $combination) {
            $signature = $combination;
            sort($signature);
            $signature = implode('-', $signature);

            if (isset($existing[$signature])) {
                continue;
            }

            $variation = Variation::create();
            $variation->ProductID = $product->ID;
            $variation->Price = $product->BasePrice;
            $variation->write();
            $variation->InternalItemID = $product->InternalItemID . '-' . $variation->ID;
            $variation->AttributeValues()->setByIDList($combination);
            $variation->write();

            $existing[$signature] = true;
            $created++;
        }

        return $created;
    }

    public function PriceRange(): ?ArrayData
    {
        $variations = $this->getOwner()->Variations();

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
     */
    public function getVariationByAttributes(array $attributes): ?Variation
    {
        if (!is_array($attributes)) {
            return null;
        }

        $attrs = array_filter(array_values($attributes));
        $set = Variation::get()->filter(['ProductID' => $this->getOwner()->ID]);

        foreach ($attrs as $i => $valueid) {
            $alias = 'A' . $i;
            $set = $set->innerJoin(
                'SilverShop_Variation_AttributeValues',
                sprintf('"SilverShop_Variation"."ID" = "%s"."SilverShop_VariationID"', $alias),
                $alias
            )->where([sprintf('"%s"."SilverShop_AttributeValueID" = ?', $alias) => $valueid]);
        }

        return $set->first();
    }

    /**
     * Generates variations based on selected attributes.
     *
     * @throws ValidationException
     */
    public function generateVariationsFromAttributes(AttributeType $attributetype, array $values): void
    {
        //TODO: introduce transactions here, in case objects get half made etc
        //if product has variation attribute types
        if ($values !== []) {
            //TODO: get values dataobject set
            $avalues = $attributetype->convertArrayToValues($values);
            $existingvariations = $this->getOwner()->Variations();
            if ($existingvariations->exists()) {
                //delete old variation, and create new ones - to prevent modification of exising variations
                foreach ($existingvariations as $oldvariation) {
                    $oldvalues = $oldvariation->AttributeValues();
                    foreach ($avalues as $value) {
                        $newvariation = $oldvariation->duplicate();
                        $newvariation->InternalItemID = $this->getOwner()->InternalItemID . '-' . $newvariation->ID;
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
                    $variation->ProductID = $this->getOwner()->ID;
                    $variation->Price = $this->getOwner()->BasePrice;
                    $variation->write();
                    $variation->InternalItemID = $this->getOwner()->InternalItemID . '-' . $variation->ID;
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
     */
    public function possibleValuesForAttributeType($type): ?DataList
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
                sprintf('TypeID = %s AND "SilverShop_Variation"."ProductID" = ', $type) . $this->getOwner()->ID
            );

        if (!Product::config()->allow_zero_price) {
            return $list->where('"SilverShop_Variation"."Price" > 0');
        }

        return $list;
    }


    public function updateFormClass(&$formClass): void
    {
        if ($this->getOwner()->Variations()->exists()) {
            $formClass = VariationForm::class;
        }
    }
}
