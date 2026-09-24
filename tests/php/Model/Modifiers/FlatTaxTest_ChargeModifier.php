<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Model\Modifiers\OrderModifier;
use SilverStripe\Dev\TestOnly;

/**
 * Test-only chargeable modifier (e.g. shipping) used by {@link FlatTaxMixedRateModifierTest}.
 */
class FlatTaxTest_ChargeModifier extends OrderModifier implements TestOnly
{
    private static string $table_name = 'SilverShop_FlatTaxTest_ChargeModifier';

    public function value($incoming): int|float
    {
        $this->Type = 'Chargable';
        return 10;
    }
}
