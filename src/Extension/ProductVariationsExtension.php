<?php

declare(strict_types=1);

namespace SilverShop\Extension;

use SilverStripe\Model\ArrayData;
use SilverStripe\Core\Validation\ValidationException;
use SilverShop\Forms\VariationForm;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\OrderItem as VariationOrderItem;
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
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
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
    private static array $db = [
        // When set, every variation of this product is priced from the product's BasePrice
        // (the per-variation price column is hidden). See Variation::sellingPrice().
        'PriceVariationsFromBase' => 'Boolean',
    ];

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
        // Live-toggle the per-variation Price column when the "price from base" checkbox changes.
        Requirements::javascript('silvershop/core:client/dist/javascript/variation-flat-pricing.js');

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
        $flatPricing = (bool) $this->getOwner()->PriceVariationsFromBase;

        // Formatted base price, shown on the flat-pricing checkbox label.
        $basePrice = ShopCurrency::create();
        $basePrice->setValue($this->getOwner()->BasePrice);
        $basePriceNice = $basePrice->Nice();

        $displayFields = [
            'InternalItemID' => [
                'title' => _t(__CLASS__ . '.CodeColumn', 'Code'),
                'callback' => fn ($record, $column, $grid): TextField =>
                    TextField::create($column)->setAttribute('style', 'width:12em'),
            ],
        ];
        // Per-variation Price column. Always rendered; hidden via CSS (the grid's "flat-pricing"
        // class) when the product prices every variation from its base price, so the JS toggle
        // below can show/hide it live without a save.
        $displayFields['Price'] = [
            'title' => _t(__CLASS__ . '.PriceColumn', 'Price'),
            'callback' => fn ($record, $column, $grid): NumericField =>
                NumericField::create($column)->setScale(2)->setAttribute('style', 'width:7em'),
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
            CheckboxField::create(
                'PriceVariationsFromBase',
                _t(
                    __CLASS__ . '.PriceVariationsFromBase',
                    'Price all variations from the base price ({price})',
                    '',
                    ['price' => $basePriceNice]
                )
            )->setDescription(_t(
                __CLASS__ . '.PriceVariationsFromBaseDesc',
                'Sells every variation at the product\'s base price and hides the per-variation price column.'
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
                // Flat pricing hides the per-variation Price column (toggled live by JS).
                . '.variations-grid.flat-pricing .col-Price{display:none}'
                // When a row is flagged "Unlimited" (optional stock module column) its stock
                // quantity no longer applies: grey the field, block editing and overlay an
                // infinity symbol. Pure CSS via :has(); a no-op when there is no such column.
                . '.variations-grid tbody tr:has(.col-StockUnlimited input:checked) td.col-StockLevel{position:relative}'
                . '.variations-grid tbody tr:has(.col-StockUnlimited input:checked) td.col-StockLevel input{color:transparent;background:#f4f4f4;pointer-events:none}'
                . '.variations-grid tbody tr:has(.col-StockUnlimited input:checked) td.col-StockLevel::after{content:"\\221E";position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);text-align:center;font-size:1.5em;line-height:1;color:#555;pointer-events:none}'
                . '</style>'
            ),
            GridField::create(
                'Variations',
                _t(__CLASS__ . '.Variations', 'Variations'),
                $this->getOwner()->Variations(),
                $variationsConfig
            )->addExtraClass('variations-grid' . ($flatPricing ? ' flat-pricing' : '')),
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
            // One constant explanation of how variation pricing works, followed by a line stating
            // the current on/off state of the "Price all variations from the base price" option.
            $explanation = _t(
                __CLASS__ . '.VariationsInfo',
                'Each variation is priced on the "Variations" tab. Tick "Price all variations from '
                . 'the base price" there to sell them all at the base price above instead.'
            );
            $state = $flatPricing
                ? _t(
                    __CLASS__ . '.VariationsFlatState',
                    'Currently on: every variation is sold at the base price above.'
                )
                : _t(
                    __CLASS__ . '.VariationsOwnPriceState',
                    'Currently off: each variation uses its own price (the base price is the fallback '
                    . 'for any left without one).'
                );
            $fields->addFieldToTab('Root.Pricing', LiteralField::create(
                'variationspriceinfo',
                $this->pricingNotice($explanation . '<br><strong>' . $state . '</strong>')
            ));
            $fields->removeFieldFromTab('Root.Main', 'InternalItemID');
        }
    }

    private function pricingNotice(string $text): string
    {
        return '<p class="message notice" style="margin-top:1.5em;display:flex;align-items:flex-start;gap:.5em">'
            . '<span class="font-icon-info-circled" aria-hidden="true"></span><span>' . $text . '</span></p>';
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

                    // Keep superseded variations that are referenced by an order: order lines
                    // link back by ProductVariationID/Version, so deleting the variation would
                    // orphan historical orders. Only the unreferenced ones are cleaned up.
                    if (VariationOrderItem::get()->filter('ProductVariationID', $oldvariation->ID)->exists()) {
                        continue;
                    }

                    $existingvariations->remove($oldvariation);
                    $oldvariation->AttributeValues()->removeAll();
                    $oldvariation->delete();
                    $oldvariation->destroy();
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
