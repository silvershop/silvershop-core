<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Variation;

use SilverShop\Forms\GridField\GridFieldVariationAttributeColumns;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;

/**
 * Covers GridFieldVariationAttributeColumns — the per-attribute-type dropdown columns
 * shown inline in the Variations grid, and their inline save.
 */
final class VariationAttributeColumnsTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/variations.yml';

    protected static bool $use_draft_site = true;

    private function variationsGrid(Product $product): GridField
    {
        $fields = $product->getCMSFields();
        // Associate with a form (as the CMS does) so the grid can resolve its owning product.
        $form = Form::create(Controller::curr(), 'EditForm', $fields, FieldList::create());
        $form->loadDataFrom($product);

        $grid = $fields->dataFieldByName('Variations');
        $this->assertInstanceOf(GridField::class, $grid);

        return $grid;
    }

    public function testAddsOneLeadingDropdownColumnPerAttributeType(): void
    {
        $product = $this->objFromFixture(Product::class, 'ball');
        $grid = $this->variationsGrid($product);

        $component = $grid->getConfig()->getComponentByType(GridFieldVariationAttributeColumns::class);
        $this->assertNotNull($component, 'the attribute-columns component is wired into the grid');

        $sizeType = $this->objFromFixture(AttributeType::class, 'size');
        $colorType = $this->objFromFixture(AttributeType::class, 'color');

        $columns = ['InternalItemID', 'Price'];
        $component->augmentColumns($grid, $columns);

        // Attribute dropdowns lead the grid (no separate combination column).
        $this->assertSame('AttributeType_' . $sizeType->ID, $columns[0]);
        $this->assertSame('AttributeType_' . $colorType->ID, $columns[1]);
        $this->assertSame(['InternalItemID', 'Price'], array_slice($columns, 2));

        // Header uses the attribute type's title.
        $meta = $component->getColumnMetadata($grid, 'AttributeType_' . $sizeType->ID);
        $this->assertSame($sizeType->Title, $meta['title']);
    }

    public function testColumnRendersADropdownPresetToTheVariationsValue(): void
    {
        $product = $this->objFromFixture(Product::class, 'ball');
        $grid = $this->variationsGrid($product);
        $component = $grid->getConfig()->getComponentByType(GridFieldVariationAttributeColumns::class);

        $sizeType = $this->objFromFixture(AttributeType::class, 'size');
        $large = $this->objFromFixture(AttributeValue::class, 'size_large');
        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');

        $html = (string) $component->getColumnContent($grid, $redLarge, 'AttributeType_' . $sizeType->ID);

        $this->assertStringContainsString('<select', $html, 'renders a dropdown');
        $this->assertStringContainsString('Small', $html, 'lists the type\'s values');
        // The variation's current value (Large) is the selected option.
        $this->assertMatchesRegularExpression(
            '/value="' . $large->ID . '"\s+selected/',
            $html,
            'the current attribute value is pre-selected'
        );
    }

    public function testInlineSavePersistsChangedAttributeValues(): void
    {
        $product = $this->objFromFixture(Product::class, 'ball');
        $grid = $this->variationsGrid($product);
        $component = $grid->getConfig()->getComponentByType(GridFieldVariationAttributeColumns::class);

        $sizeType = $this->objFromFixture(AttributeType::class, 'size');
        $colorType = $this->objFromFixture(AttributeType::class, 'color');
        $small = $this->objFromFixture(AttributeValue::class, 'size_small');
        $red = $this->objFromFixture(AttributeValue::class, 'color_red');
        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');

        // Simulate the grid POST: change Size to Small, keep Colour as Red (the form submits
        // every dropdown in the row).
        $grid->setValue([
            'VariationAttributes' => [
                $redLarge->ID => [
                    $sizeType->ID => (string) $small->ID,
                    $colorType->ID => (string) $red->ID,
                ],
            ],
        ]);

        $component->handleSave($grid, $product);

        $reloaded = Variation::get()->byID($redLarge->ID);
        $valueIDs = $reloaded->AttributeValues()->sort('ID')->column('ID');
        sort($valueIDs);
        $expected = [$small->ID, $red->ID];
        sort($expected);

        $this->assertSame($expected, $valueIDs, 'the variation now carries the newly selected values');
    }
}
