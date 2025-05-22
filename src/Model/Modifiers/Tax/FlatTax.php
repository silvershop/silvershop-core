<?php

namespace SilverShop\Model\Modifiers\Tax;

use SilverShop\Model\Order;

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
        $this->Rate = self::config()->rate;
        //inclusive tax requires a different calculation
        return self::config()->exclusive
            ?
            $incoming * $this->Rate
            :
            $incoming - round($incoming / (1 + $this->Rate), Order::config()->rounding_precision);
    }
}
