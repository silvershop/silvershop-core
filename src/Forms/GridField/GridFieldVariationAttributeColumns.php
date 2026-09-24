<?php

declare(strict_types=1);

namespace SilverShop\Forms\GridField;

use SilverShop\Model\Variation\AttributeType;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_SaveHandler;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\List\SS_List;
use SilverStripe\ORM\DataObjectInterface;

/**
 * Adds one inline-editable dropdown column per {@link AttributeType} of the product to the
 * Variations grid, so each variation's attribute value (e.g. Size, Colour) can be seen and
 * changed directly in the row — the "attribute dropdown loop" of the matrix editor.
 *
 * Self-contained (renders the dropdown pre-set to the row's current value and reads the nested
 * POST on save), because the per-type column names are data-driven and can't route through a
 * static getter/setter the way {@link GridFieldEditableColumns} requires.
 */
class GridFieldVariationAttributeColumns implements GridField_ColumnProvider, GridField_SaveHandler
{
    private const POST_KEY = 'VariationAttributes';

    private const COLUMN_PREFIX = 'AttributeType_';

    /**
     * The attribute types that define the columns — taken from the product owning the grid.
     */
    private function attributeTypes(GridField $grid): SS_List
    {
        $form = $grid->getForm();
        $product = $form ? $form->getRecord() : null;

        if ($product && $product->hasMethod('VariationAttributeTypes')) {
            return $product->VariationAttributeTypes();
        }

        return ArrayList::create();
    }

    private function columnToTypeID(string $column): ?int
    {
        if (preg_match('/^' . self::COLUMN_PREFIX . '(\d+)$/', $column, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function augmentColumns($gridField, &$columns): void
    {
        $attributeColumns = [];
        foreach ($this->attributeTypes($gridField) as $type) {
            $attributeColumns[] = self::COLUMN_PREFIX . $type->ID;
        }

        if ($attributeColumns === []) {
            return;
        }

        // Lead with the attribute dropdowns (or sit right after the "Variation" combination
        // column when one is present) so each row is identified by its attribute values.
        $position = array_search('Title', $columns, true);
        $insertAt = $position === false ? 0 : $position + 1;
        array_splice($columns, $insertAt, 0, $attributeColumns);
    }

    public function getColumnsHandled($gridField): array
    {
        $columns = [];
        foreach ($this->attributeTypes($gridField) as $type) {
            $columns[] = self::COLUMN_PREFIX . $type->ID;
        }

        return $columns;
    }

    public function getColumnMetadata($gridField, $column): array
    {
        $typeID = $this->columnToTypeID($column);
        $type = $typeID ? AttributeType::get()->byID($typeID) : null;

        return ['title' => $type ? $type->Title : $column];
    }

    public function getColumnAttributes($gridField, $record, $column): array
    {
        return ['class' => 'col-' . $column];
    }

    public function getColumnContent($gridField, $record, $column): ?string
    {
        $typeID = $this->columnToTypeID($column);
        $type = $typeID ? AttributeType::get()->byID($typeID) : null;
        if (!$type) {
            return null;
        }

        $current = $record->AttributeValues()->filter('TypeID', $typeID)->first();

        $dropdown = DropdownField::create(
            sprintf('%s[%s][%d][%d]', $gridField->getName(), self::POST_KEY, $record->ID, $typeID),
            $type->Title,
            $type->Values()->map('ID', 'Value')->toArray()
        )
            ->setValue($current ? $current->ID : null)
            ->setEmptyString('—')
            // The admin resets native <select> chrome (appearance:none) without re-adding a
            // chevron here; force the native control back so the dropdown arrow shows.
            ->setAttribute('style', 'appearance:auto;-webkit-appearance:menulist;background-image:none');

        if ($gridField->isReadonly() || !$record->canEdit()) {
            $dropdown = $dropdown->performReadonlyTransformation();
        }

        return $dropdown->forTemplate();
    }

    public function handleSave(GridField $gridField, DataObjectInterface $record): void
    {
        $value = $gridField->getValue();
        if (empty($value[self::POST_KEY]) || !is_array($value[self::POST_KEY])) {
            return;
        }

        $list = $gridField->getList();

        foreach ($value[self::POST_KEY] as $variationID => $typeMap) {
            if (!is_numeric($variationID) || !is_array($typeMap)) {
                continue;
            }

            $variation = $list->byID((int) $variationID);
            if (!$variation || !$variation->canEdit()) {
                continue;
            }

            $valueIDs = [];
            foreach ($typeMap as $valueID) {
                if ($valueID) {
                    $valueIDs[] = (int) $valueID;
                }
            }

            $variation->AttributeValues()->setByIDList($valueIDs);
        }
    }
}
