<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Model\Modifiers\OrderModifier;
use SilverStripe\Dev\TestOnly;

/**
 * Test-only deductible modifier (e.g. a discount) used by {@link FlatTaxMixedRateModifierTest}.
 */
class FlatTaxTest_DiscountModifier extends OrderModifier implements TestOnly
{
    private static string $table_name = 'SilverShop_FlatTaxTest_DiscountModifier';

    public function value($incoming): int|float
    {
        $this->Type = 'Deductable';
        return 40;
    }
}
