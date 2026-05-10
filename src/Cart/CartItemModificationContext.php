<?php

declare(strict_types=1);

namespace SilverShop\Cart;

use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Model\OrderItem;

/**
 * Describes a pending cart line change for validation via
 * {@see Order::extend('updateCartItemModification', ...)}.
 *
 * Operation semantics:
 * - {@see self::OP_ADD}: `primaryQuantity` is the add delta. `proposedLineTotalQuantity` is the line total if the change applies.
 * - {@see self::OP_SET_QUANTITY}: both quantities are the requested absolute line total.
 * - {@see self::OP_REMOVE}: `primaryQuantity` is the number of units to remove (if the whole line is removed, this equals the
 *   current line quantity). `proposedLineTotalQuantity` is the remaining quantity on the line (0 means delete).
 * - {@see self::OP_SWITCH_VARIATION}: `primaryQuantity` is unused (0). `proposedLineTotalQuantity` is the line total after
 *   switch (usually unchanged).
 */
final class CartItemModificationContext
{
    public const OP_ADD = 'add';

    public const OP_SET_QUANTITY = 'set_quantity';

    public const OP_REMOVE = 'remove';

    public const OP_SWITCH_VARIATION = 'switch_variation';

    public function __construct(
        public readonly string $operation,
        public readonly Order $order,
        public readonly ?OrderItem $orderItem,
        public readonly ?Buyable $buyable,
        public readonly array $filter,
        public readonly int $primaryQuantity,
        public readonly int $proposedLineTotalQuantity,
    ) {
    }
}
