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
        $this->Type = self::config()->exclusive ? 'Chargable' : 'Ignored';
    }

    /**
     * Get the tax amount to charge on the order.
     */
    public function value($incoming): int|float
    {
        $this->Rate = (float) self::config()->rate;
        $order = $this->Order();
        $taxTotal = 0.0;
        $hasCustomTaxRateFound = false;

        if ($order && $order->exists() && $order->Items()->exists()) {
            foreach ($order->Items() as $item) {
                [$taxRate, $hasCustomTaxRateForItem] = $this->getItemTaxRate($item);
                if ($hasCustomTaxRateForItem) {
                    $hasCustomTaxRateFound = true;
                }

                $taxTotal += $this->calculateTaxForAmount((float) $item->Total(), $taxRate);
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

        if (self::config()->exclusive) {
            return $amount * $rate;
        }

        return $amount - round($amount / (1 + $rate), Order::config()->rounding_precision);
    }
}
