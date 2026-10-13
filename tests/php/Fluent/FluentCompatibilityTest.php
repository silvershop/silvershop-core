<?php

declare(strict_types=1);

namespace SilverShop\Tests\Fluent;

use SilverShop\Model\Product\OrderItem;
use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\OrderItem as VariationOrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use TractorCow\Fluent\Extension\FluentExtension;
use TractorCow\Fluent\Extension\FluentSiteTreeExtension;
use TractorCow\Fluent\Extension\FluentVersionedExtension;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

/**
 * Regression tests for running SilverShop alongside tractorcow/silverstripe-fluent.
 * Skipped when Fluent isn't installed.
 */
final class FluentCompatibilityTest extends SapphireTest
{
    protected $usesDatabase = true;

    public static function getRequiredExtensions()
    {
        if (!class_exists(FluentState::class)) {
            return [];
        }

        return [
            SiteTree::class => [FluentSiteTreeExtension::class],
            Variation::class => [FluentVersionedExtension::class],
            AttributeValue::class => [FluentExtension::class],
            AttributeType::class => [FluentExtension::class],
        ];
    }

    protected function setUp(): void
    {
        if (!class_exists(FluentState::class)) {
            $this->markTestSkipped('tractorcow/silverstripe-fluent is not installed');
        }

        parent::setUp();

        Locale::create(['Locale' => 'en_NZ', 'Title' => 'English', 'URLSegment' => 'en', 'IsGlobalDefault' => true])->write();
        Locale::create(['Locale' => 'de_DE', 'Title' => 'German', 'URLSegment' => 'de'])->write();
        Locale::clearCached();
    }

    public function testCurrentLocaleFallsBackToI18nWithoutFluentLocale(): void
    {
        FluentState::singleton()->withState(function (FluentState $state): void {
            $state->setLocale(null);
            $this->assertNotEmpty(ShopTools::get_current_locale());
        });
    }

    /**
     * @link https://github.com/silvershop/silvershop-core/issues/774
     */
    public function testVariationsWorkInUntranslatedLocale(): void
    {
        [$product, $size, $color] = FluentState::singleton()->withState(function (FluentState $state): array {
            $state->setLocale('en_NZ');
            $product = Product::create(['Title' => 'Ball', 'InternalItemID' => 'BALL', 'BasePrice' => 10]);
            $product->write();

            $size = AttributeType::create(['Name' => 'Size', 'Label' => 'Size']);
            $size->write();
            $color = AttributeType::create(['Name' => 'Color', 'Label' => 'Color']);
            $color->write();
            foreach (['Small', 'Large'] as $sort => $value) {
                AttributeValue::create(['Value' => $value, 'TypeID' => $size->ID, 'Sort' => $sort])->write();
            }
            foreach (['Red', 'Blue'] as $sort => $value) {
                AttributeValue::create(['Value' => $value, 'TypeID' => $color->ID, 'Sort' => $sort])->write();
            }
            $product->VariationAttributeTypes()->addMany([$size, $color]);

            return [$product, $size, $color];
        });

        FluentState::singleton()->withState(function (FluentState $state) use ($product, $size, $color): void {
            $state->setLocale('de_DE');
            $product = Product::get()->byID($product->ID);

            // Creating variations, as the CMS "Generate variations" button does
            $this->assertSame(4, $product->generateVariations());

            $variations = $product->Variations();
            $this->assertSame(4, $variations->count());
            $this->assertNotNull($variations->first());
            $codes = $variations->column('InternalItemID');
            $sorted = $codes;
            sort($sorted);
            $this->assertSame($sorted, $codes, 'Variations are sorted by product code');

            $this->assertSame(['Small', 'Large', 'Red', 'Blue'], AttributeValue::get()->column('Value'));
            $this->assertSame(2, $product->possibleValuesForAttributeType($size)->count());

            $small = $size->Values()->find('Value', 'Small');
            $blue = $color->Values()->find('Value', 'Blue');
            $variation = $product->getVariationByAttributes([$small->ID, $blue->ID]);
            $this->assertNotNull($variation);

            // Product edit form with the variations grid, as rendered in the catalog admin
            $grid = $product->getCMSFields()->dataFieldByName('Variations');
            $this->assertNotNull($grid);
            $this->assertCount(4, $grid->getManipulatedList()->toArray());
        });
    }

    /**
     * @link https://github.com/silvershop/silvershop-core/issues/748
     */
    public function testOrderItemFindsProductVersionSavedInAnotherLocale(): void
    {
        [$item, $variationItem] = FluentState::singleton()->withState(function (FluentState $state): array {
            $state->setLocale('en_NZ');
            $product = Product::create(['Title' => 'Ball', 'BasePrice' => 10]);
            $product->write();
            $product->publishSingle();

            $variation = Variation::create(['ProductID' => $product->ID, 'InternalItemID' => 'BALL-RED']);
            $variation->write();

            $item = OrderItem::create(['ProductID' => $product->ID, 'ProductVersion' => $product->Version]);
            $item->write();

            $variationItem = VariationOrderItem::create([
                'ProductID' => $product->ID,
                'ProductVersion' => $product->Version,
                'ProductVariationID' => $variation->ID,
                'ProductVariationVersion' => $variation->Version,
            ]);
            $variationItem->write();

            return [$item, $variationItem];
        });

        FluentState::singleton()->withState(function (FluentState $state) use ($item, $variationItem): void {
            $state->setLocale('de_DE');

            $product = $item->Product();
            $this->assertNotNull($product);
            $this->assertSame('Ball', $product->Title);

            $variation = $variationItem->ProductVariation();
            $this->assertNotNull($variation);
            $this->assertSame('BALL-RED', $variation->InternalItemID);
        });
    }

    public function testOrderItemProductCanBeUpdatedByExtension(): void
    {
        OrderItem::add_extension(OrderItemProductExtension::class);
        try {
            $item = OrderItem::create(['ProductID' => 123, 'ProductVersion' => 1]);
            $this->assertSame('Replacement', $item->Product()->Title);
        } finally {
            OrderItem::remove_extension(OrderItemProductExtension::class);
        }
    }
}
