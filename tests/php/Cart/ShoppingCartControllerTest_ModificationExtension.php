<?php

declare(strict_types=1);

namespace SilverShop\Tests\Cart;

use SilverShop\Cart\CartItemModificationContext;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
final class ShoppingCartControllerTest_ModificationExtension extends Extension implements TestOnly
{
    /**
     * @param \SilverShop\Cart\CartItemModificationContext $context
     * @param \SilverShop\Cart\CartItemModificationOutcome $outcome
     */
    public function updateCartItemModification($context, $outcome): void
    {
        if ($context->operation !== CartItemModificationContext::OP_SWITCH_VARIATION) {
            return;
        }

        if ($context->proposedLineTotalQuantity > 5) {
            $outcome->lineQuantityAfter = 5;
            $outcome->message = 'Clamped for test';
            $outcome->messageType = 'warning';
        }
    }
}
