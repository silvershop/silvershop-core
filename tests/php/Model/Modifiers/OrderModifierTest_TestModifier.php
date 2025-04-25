<?php

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Model\Modifiers\OrderModifier;
use SilverStripe\Dev\TestOnly;

class OrderModifierTest_TestModifier extends OrderModifier implements TestOnly
{
    private static string $table_name = 'SilverShop_OrderModifierTest_TestModifier';

    public static int $value = 10;
    private bool $willFail = false;

    public function value($incoming): int
    {
        if (self::$value === 42) {
            $this->willFail = true;
        }
        return self::$value;
    }

    protected function onAfterWrite(): void
    {
        parent::onAfterWrite();
        if ($this->willFail) {
            user_error('Modifier failure!');
        }
    }
}
