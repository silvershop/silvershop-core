<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * [PoC] Strict state-machine stand-in: vetoes (rejects) any transition
 * whose target equals $vetoTarget by returning false from the funnel hook.
 * Proves that onBeforeStatusChange can enforce a guard before the write.
 *
 * @extends Extension<static>
 */
class OrderStatusFunnelTest_VetoExtension extends Extension implements TestOnly
{
    public static ?string $vetoTarget = null;

    public function onBeforeStatusChange($from, $to, ?string $name)
    {
        if (self::$vetoTarget !== null && $to === self::$vetoTarget) {
            return false; // reject the transition
        }
        return null; // allow
    }
}
