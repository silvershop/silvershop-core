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
    public static $disable_theme  = true;
    protected static $use_draft_site = true;

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $ball;

    /**
     * @var Variation
     */
    protected $redlarge;

    public function setUp()
    {
        parent::setUp();
        $this->ball = $this->objFromFixture(Product::class, "ball");
        $this->mp3player = $this->objFromFixture(Product::class, "mp3player");
        $this->redlarge = $this->objFromFixture(Variation::class, "redlarge");
    }

    public function testVariationsPersistOnUnpublish()
    {
        $color = $this->objFromFixture(AttributeType::class, "color");
        $values = array('Black', 'Blue');
        $this->mp3player->generateVariationsFromAttributes($color, $values);
        $this->mp3player->publishSingle();

        $this->mp3player->publish('Stage', 'Stage');

        $variations = $this->mp3player->Variations();
        $this->assertEquals($variations->Count(), 2, "two variations created and persist after product unpublished");
    }
}
