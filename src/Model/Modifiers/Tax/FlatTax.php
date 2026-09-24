<?php

declare(strict_types=1);

namespace SilverShop\Model\Modifiers\Tax;

use InvalidArgumentException;
use SilverShop\Model\Order;
use SilverShop\Model\OrderItem;
use SilverStripe\ORM\DataObject;

/**
 * Handles calculation of sales tax on Orders.
 *
 * @package    shop
 * @subpackage modifiers
 */
class FlatTax extends Base
{
    private static string $name            = 'GST';

    /**
     * @config
     * @var float
     */
    private static $rate            = 0.15;

    private static bool $exclusive       = true;

    private static string $includedmessage = '%.1f%% %s (inclusive)';

    private static string $excludedmessage = '%.1f%% %s';

    private static string $table_name = 'SilverShop_FlatTaxModifier';

    public function __construct($record = null, $isSingleton = false, $model = null)
    {
        parent::__construct($record, $isSingleton, $model);
        $this->Type = self::config()->get('exclusive') ? 'Chargable' : 'Ignored';
    }

    /**
     * Get the tax amount to charge on the order.
     */
    public function value($incoming): int|float
    {
        $this->Rate = (float) self::config()->get('rate');
        $order = $this->Order();
        $taxTotal = 0.0;
        $hasCustomTaxRateFound = false;

        if ($order && $order->exists() && $order->Items()->exists()) {
            // Order-level discounts (deductible modifiers) reduce the taxable base, so they
            // must be apportioned across the items before per-item tax is calculated —
            // otherwise mixed / product-specific rates over-charge tax on discounted orders.
            // Spread the discount across items in proportion to each item's value.
            // (Chargeable modifiers such as shipping are intentionally not taxed here; their
            // taxability is jurisdiction-specific and belongs in dedicated tax configuration.)
            $itemSubtotal = (float) $order->SubTotal();
            $discount = (float) $order->Modifiers()->filter('Type', 'Deductable')->sum('Amount');

            foreach ($order->Items() as $item) {
                [$taxRate, $hasCustomTaxRateForItem] = $this->getItemTaxRate($item);
                if ($hasCustomTaxRateForItem) {
                    $hasCustomTaxRateFound = true;
                }

                $itemTotal = (float) $item->Total();
                $taxableAmount = $itemSubtotal > 0
                    ? $itemTotal - (($itemTotal / $itemSubtotal) * $discount)
                    : $itemTotal;

                $taxTotal += $this->calculateTaxForAmount($taxableAmount, $taxRate);
            }
        }

        if ($hasCustomTaxRateFound) {
            return $taxTotal;
        }

        return $this->calculateTaxForAmount((float) $incoming, $this->Rate);
    }

    protected function getItemTaxRate(OrderItem $item): array
    {
        $buyable = $item->Buyable();
        if (!$buyable instanceof DataObject || !method_exists($buyable, 'getTaxRate')) {
            return [$this->Rate, false];
        }

        $itemTaxRate = $buyable->getTaxRate();
        if ($itemTaxRate === null) {
            return [$this->Rate, false];
        }

        $itemTaxRate = (float) $itemTaxRate;
        // Defensive check for legacy or direct DB data that bypassed Product validation.
        if ($itemTaxRate < 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'Tax rate for product #%d ("%s") must not be negative.',
                    (int) $buyable->ID,
                    (string) $buyable->Title
                )
            );
        }

        return [$itemTaxRate, true];
    }

    protected function calculateTaxForAmount(float $amount, float $rate): float
    {
        if ($rate === 0.0) {
            return 0.0;
        }

        if (self::config()->get('exclusive')) {
            return $amount * $rate;
        }

        return $amount - round($amount / (1 + $rate), Order::config()->get('rounding_precision'));
    }
}
