<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Observer: records both the new pre-write funnel hook
 * (onBeforeStatusChange) and the existing post hook (onStatusChange),
 * so a test can prove (a) the funnel fires with the transition name and
 * (b) the legacy onStatusChange still fires (BC).
 *
 * @extends Extension<static>
 */
class OrderStatusFunnelTest_RecordingExtension extends Extension implements TestOnly
{
    /** @var array<int,array{from:mixed,to:mixed,name:?string}> */
    public static array $pre = [];

    /** @var array<int,array{from:mixed,to:mixed}> */
    public static array $post = [];

    public static function reset(): void
    {
        self::$pre = [];
        self::$post = [];
    }

    // New assignment-time funnel hook. Observer only -> returns nothing (no veto).
    public function onBeforeStatusChange($from, $to, ?string $name): void
    {
        self::$pre[] = ['from' => $from, 'to' => $to, 'name' => $name];
    }

    // Existing write-time hook (unchanged contract).
    public function onStatusChange($from, $to): void
    {
        self::$post[] = ['from' => $from, 'to' => $to];
    }
}
