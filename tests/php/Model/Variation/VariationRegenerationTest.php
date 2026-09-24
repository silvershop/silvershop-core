<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Variation;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\AttributeValue;
use SilverShop\Model\Variation\OrderItem as VariationOrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Dev\SapphireTest;

/**
 * Regenerating a product's variation matrix (adding an attribute axis) must not
 * delete variations that are referenced by an order — order lines link back by
 * ProductVariationID/Version, so destroying the variation orphans historical orders.
 */
final class VariationRegenerationTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../../Fixtures/variations.yml';

    protected static bool $use_draft_site = true;

    public function testRegenerationPreservesOrderReferencedVariations(): void
    {
        $ball = $this->objFromFixture(Product::class, 'ball');
        $redLarge = $this->objFromFixture(Variation::class, 'redLarge');
        $redLargeId = $redLarge->ID;

        // Simulate an order line that bought this exact variation.
        $orderItem = $redLarge->createItem(1);
        $orderItem->ProductVariationID = $redLargeId;
        $orderItem->write();
        $this->assertTrue(
            VariationOrderItem::get()->filter('ProductVariationID', $redLargeId)->exists(),
            'precondition: the variation is referenced by an order item'
        );

        // Add a new axis (Capacity) — this regenerates the variation matrix.
        $capacity = $this->objFromFixture(AttributeType::class, 'capacity');
        $c60 = $this->objFromFixture(AttributeValue::class, 'capacity_60');
        $c120 = $this->objFromFixture(AttributeValue::class, 'capacity_120');
        $ball->generateVariationsFromAttributes($capacity, ['60GB', '120GB']);

        // The order-referenced variation must still exist, or the order line is orphaned.
        $this->assertNotNull(
            Variation::get()->byID($redLargeId),
            'A variation referenced by an order item must not be deleted when regenerating variations.'
        );
    }

    public function testRegenerationStillExpandsTheMatrix(): void
    {
        $ball = $this->objFromFixture(Product::class, 'ball');
        $before = $ball->Variations()->count();

        $capacity = $this->objFromFixture(AttributeType::class, 'capacity');
        $c60 = $this->objFromFixture(AttributeValue::class, 'capacity_60');
        $c120 = $this->objFromFixture(AttributeValue::class, 'capacity_120');
        $ball->generateVariationsFromAttributes($capacity, ['60GB', '120GB']);

        // New combinations carrying a capacity value were generated.
        $withCapacity = Variation::get()
            ->filter('ProductID', $ball->ID)
            ->filterAny('AttributeValues.ID', [$c60->ID, $c120->ID]);
        $this->assertGreaterThan(0, $withCapacity->count(), 'capacity combinations should be generated');
        $this->assertGreaterThanOrEqual($before, $ball->Variations()->count());
    }
}
