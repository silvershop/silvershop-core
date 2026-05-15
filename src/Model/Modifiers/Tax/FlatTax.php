<?php

declare(strict_types=1);

namespace SilverShop\Model\Modifiers\Tax;

use SilverShop\Model\Order;
use SilverShop\Model\OrderItem;

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
        $hasCustomTaxRate = false;

        if ($order && $order->exists() && $order->Items()->exists()) {
            foreach ($order->Items() as $item) {
                $taxRate = $this->getItemTaxRate($item, $hasCustomTaxRate);
                $taxTotal += $this->calculateTaxForAmount((float) $item->Total(), $taxRate);
            }
        }

        if ($hasCustomTaxRate) {
            return $taxTotal;
        }

        return $this->calculateTaxForAmount((float) $incoming, $this->Rate);
    }

    protected function getItemTaxRate(OrderItem $item, bool &$hasCustomTaxRate): float
    {
        $buyable = $item->Buyable();
        if (!$buyable || !method_exists($buyable, 'getField')) {
            return $this->Rate;
        }

        $itemTaxRate = $buyable->getField('TaxRate');
        if ($itemTaxRate === null || $itemTaxRate === '') {
            return $this->Rate;
        }

        $hasCustomTaxRate = true;
        return max(0.0, (float) $itemTaxRate);
    }

    protected function calculateTaxForAmount(float $amount, float $rate): float
    {
        if (self::config()->exclusive) {
            return $amount * $rate;
        }

        if ($rate <= 0) {
            return 0.0;
        }

        return $amount - round($amount / (1 + $rate), Order::config()->rounding_precision);
    }
}
