<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Page\Product;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 *
 * @subpackage tasks
 */
class ProductVariationsFromAttributeCombinations extends BuildTask
{
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $products = Product::get();
        if (!$products->count()) {
            return Command::SUCCESS;
        }

        foreach ($products as $product) {
            /** @var AttributeType $attributeType */
            foreach ($product->VariationAttributeTypes() as $attributeType) {
                $values = $attributeType->Values()->column('Value');
                if (!empty($values)) {
                    $product->generateVariationsFromAttributes($attributeType, $values);
                }
            }
        }

        return Command::SUCCESS;
    }
}
