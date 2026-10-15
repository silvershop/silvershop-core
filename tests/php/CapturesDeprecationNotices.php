<?php

declare(strict_types=1);

namespace SilverShop\Tests;

use ReflectionProperty;
use SilverStripe\Dev\Deprecation;

/**
 * Lets a SapphireTest assert which deprecation notices some code raises.
 */
trait CapturesDeprecationNotices
{
    /**
     * Run the callback with deprecation notices enabled, and return the notices it raised.
     *
     * @return string[]
     */
    protected function captureDeprecationNotices(callable $callback): array
    {
        $buffer = new ReflectionProperty(Deprecation::class, 'userErrorMessageBuffer');
        $wasEnabled = Deprecation::isEnabled();
        Deprecation::enable();
        if (!Deprecation::isEnabled()) {
            $this->markTestSkipped('Deprecation notices cannot be enabled in this environment');
        }
        $buffer->setValue(null, []);
        try {
            $callback();
            return array_column($buffer->getValue(), 'message');
        } finally {
            $buffer->setValue(null, []);
            if (!$wasEnabled) {
                Deprecation::disable();
            }
        }
    }

    /**
     * Filter notices down to those about the given method, e.g. "SilverShop\Model\Order->Total".
     *
     * @param  string[] $notices
     * @return string[]
     */
    protected function noticesFor(array $notices, string $method): array
    {
        return array_values(array_filter($notices, fn ($notice) => str_contains($notice, $method . ' is deprecated')));
    }
}
