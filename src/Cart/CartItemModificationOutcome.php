<?php

declare(strict_types=1);

namespace SilverShop\Cart;

/**
 * Populated by extensions on {@see Order} via `updateCartItemModification` to validate or adjust cart line updates.
 *
 * Extensions may:
 * - Set {@see self::$abort} to true and set {@see self::$message} to block the operation (HTTP/form feedback uses
 *   {@see ShoppingCart::getMessage()} / {@see ShoppingCart::getMessageType()}).
 * - Set {@see self::$lineQuantityAfter} to clamp or adjust the line quantity (non‑negative). Applies to add, set quantity,
 *   remove, and variation switch.
 * - Set {@see self::$message} with {@see self::$abort} false to provide non‑blocking feedback (for example a warning that
 *   quantity was reduced).
 */
final class CartItemModificationOutcome
{
    public bool $abort = false;

    public ?string $message = null;

    public string $messageType = 'good';

    /**
     * When not null, replaces the computed line quantity for the operation (0 removes the line where applicable).
     */
    public ?int $lineQuantityAfter = null;
}
