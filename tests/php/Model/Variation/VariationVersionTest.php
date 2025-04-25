<?php

namespace SilverShop\Tests\Model\Variation;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Dev\SapphireTest;

/**
 * Test product variation capabilities.
 *
 * @link       ProductVariation
 * @link       ProductVariationDecorator
 * @package    shop
 * @subpackage tests
 */
class VariationVersionTest extends SapphireTest
{
    public static $fixture_file   = '../../Fixtures/variations.yml';
    public static bool $disable_theme  = true;
    protected static bool $use_draft_site = true;

    protected Product $mp3player;
    protected Product $ball;
    protected Variation $redLarge;

    public function setUp(): void
    {
        parent::setUp();
        $this->ball = $this->objFromFixture(Product::class, "ball");
        $this->mp3player = $this->objFromFixture(Product::class, "mp3player");
        $this->redLarge = $this->objFromFixture(Variation::class, "redLarge");
    }

    public function testVariationsPersistOnUnpublish(): void
    {
        $color = $this->objFromFixture(AttributeType::class, "color");
        $values = ['Black', 'Blue'];

        $this->mp3player->generateVariationsFromAttributes($color, $values);
        $this->mp3player->publishRecursive();

        $variations = $this->mp3player->Variations();
        $this->assertEquals($variations->Count(), 2, "two variations created and persist after product unpublished");
    }
}
