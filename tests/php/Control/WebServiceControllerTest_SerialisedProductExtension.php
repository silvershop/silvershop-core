<?php

declare(strict_types=1);

namespace SilverShop\Tests\Control;

use SilverShop\Page\Product;
use SilverStripe\Core\Extension;

class WebServiceControllerTest_SerialisedProductExtension extends Extension
{
    public static bool $enabled = false;

    /**
     * @param array<string, mixed> $payload
     */
    public function updateSerialisedProduct(array &$payload, Product $product): void
    {
        if (!self::$enabled) {
            return;
        }

        $payload['customTag'] = 'custom-' . $product->URLSegment;
    }
}
