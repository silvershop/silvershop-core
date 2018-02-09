<?php

namespace SilverShop\Tests\Model\Modifiers;

use SilverShop\Model\Modifiers\OrderModifier;
use SilverStripe\Dev\TestOnly;

class OrderModifierTest_TestModifier extends OrderModifier implements TestOnly
{
    public static $value = 10;
    private $willFail = false;

    public function value($incoming)
    {
        if (self::$value === 42) {
            $this->willFail = true;
        }
        return self::$value;
    }

    protected function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->willFail) {
            user_error('Modifier failure!');
        }
    }
}
