<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Variation;

use SilverShop\Forms\GridField\GridFieldGenerateVariationsButton;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

/**
 * Covers Product::generateVariations() — the idempotent, non-destructive matrix generator
 * behind the CMS "Generate variations" button.
 */
final class GenerateVariationsTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/variations.yml';

    protected static bool $use_draft_site = true;

    public function testGeneratesFullMatrix(): void
    {
        // mp3player varies by Capacity (60GB, 120GB) × Color (Red, Blue, Yellow) = 6 combinations.
        $product = $this->objFromFixture(Product::class, 'mp3player');
        $this->assertFalse($product->Variations()->exists(), 'starts with no variations');

        $created = $product->generateVariations();

        $this->assertSame(6, $created);
        $this->assertSame(6, $product->Variations()->count());
        // Each generated variation is a complete combination (one value per axis).
        foreach ($product->Variations() as $variation) {
            $this->assertSame(2, $variation->AttributeValues()->count());
        }
    }

    public function testIsIdempotentAndNonDestructive(): void
    {
        $product = $this->objFromFixture(Product::class, 'mp3player');
        $product->generateVariations();
        $ids = $product->Variations()->sort('ID')->column('ID');

        $secondRun = $product->generateVariations();

        $this->assertSame(0, $secondRun, 're-running generates nothing');
        $this->assertSame(6, $product->Variations()->count(), 'no duplicate variations');
        $this->assertSame($ids, $product->Variations()->sort('ID')->column('ID'), 'existing variations are untouched');
    }

    /**
     * Smoke test the CMS matrix editor: the Variations tab builds, is wired with the
     * inline editable columns + generate button, and the columns render for a record.
     */
    public function testVariationsTabBuildsAndRenders(): void
    {
        $product = $this->objFromFixture(Product::class, 'mp3player');
        $product->generateVariations();

        $fields = $product->getCMSFields();
        // The auto-scaffolded (empty) "Prices" has_many tab is suppressed.
        $this->assertNull($fields->fieldByName('Root.Prices'), 'the empty Prices tab should be suppressed');
        // Associate the fields with a form (as the CMS does) so grid fields can render.
        $form = Form::create(Controller::curr(), 'EditForm', $fields, FieldList::create());
        $form->loadDataFrom($product);

        $grid = $fields->dataFieldByName('Variations');
        $this->assertInstanceOf(GridField::class, $grid);

        $config = $grid->getConfig();
        $this->assertNotNull(
            $config->getComponentByType(GridFieldGenerateVariationsButton::class),
            'the Generate variations button is present'
        );
        $editable = $config->getComponentByType(GridFieldEditableColumns::class);
        $this->assertNotNull($editable, 'inline editable columns are present');

        // Render the editable columns for a record — this exercises the display-field
        // closures and would fail on a config error.
        $variation = $product->Variations()->first();
        $this->assertNotSame('', (string) $editable->getColumnContent($grid, $variation, 'InternalItemID'));
        $this->assertNotSame('', (string) $editable->getColumnContent($grid, $variation, 'Price'));
    }
}
