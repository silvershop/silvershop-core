<?php

declare(strict_types=1);

namespace SilverShop\Tests\Tasks;

use SilverShop\Page\Product;
use SilverShop\Tasks\PopulateShopTask;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * The task builds the demo shop and leaves the allowed countries alone. It used to seed a 2012 comma-separated country
 * list, which the shop can't read since `AllowedCountries` is stored as JSON: every country stayed allowed, while
 * Settings > Shop showed no country ticked.
 */
class PopulateShopTaskTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testItPopulatesTheShopAndLeavesTheAllowedCountriesAlone(): void
    {
        $task = PopulateShopTask::create();
        $task->run(
            new ArrayInput([], new InputDefinition($task->getOptions())),
            new PolyOutput(PolyOutput::FORMAT_ANSI, wrappedOutput: new BufferedOutput())
        );

        $this->assertGreaterThan(0, Product::get()->count(), 'demo products created');

        $config = SiteConfig::current_site_config();
        $this->assertEmpty($config->getField('AllowedCountries'));
        $this->assertGreaterThan(200, count($config->getCountriesList()), 'every country allowed');
    }
}
