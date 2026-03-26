<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Page\Product;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Add 5 random Live products to cart, with random quantities between 1 and 10.
 */
class PopulateCartTask extends BuildTask
{
    protected string $title = 'Populate Cart';

    protected static string $description = 'Add 5 random Live products or variations to cart, with random quantities between 1 and 10.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $shoppingCart = ShoppingCart::singleton();
        $count = 5;
        $allIds = Versioned::get_by_stage(Product::class, Versioned::LIVE)->column('ID');
        shuffle($allIds);
        $selectedIds = array_slice($allIds, 0, $count);
        $products = Product::get()->filter('ID', $selectedIds);
        if ($products->exists()) {
            foreach ($products as $product) {
                $variations = $product->Variations();
                if ($variations->exists()) {
                    $variationIds = $variations->column('ID');
                    shuffle($variationIds);
                    $product = $variations->filter('ID', $variationIds[0])->first();
                }

                $quantity = rand(1, 5);
                if ($product->canPurchase(Security::getCurrentUser(), $quantity)) {
                    $shoppingCart->add($product, $quantity);
                }
            }
        }

        $output->writeln('Cart populated with ' . $count . ' random products.');
        return Command::SUCCESS;
    }
}