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
    /**
     * @config
     * @var string
     */
    private static $name            = 'GST';

    /**
     * @config
     * @var float
     */
    private static $rate            = 0.15;

    /**
     * @config
     * @var bool
     */
    private static $exclusive       = true;

    /**
     * @config
     * @var string
     */
    private static $includedmessage = '%.1f%% %s (inclusive)';

    /**
     * @config
     * @var string
     */
    private static $excludedmessage = '%.1f%% %s';

    public function __construct($record = null, $isSingleton = false, $model = null)
    {
        parent::__construct($record, $isSingleton, $model);
        $this->Type = self::config()->exclusive ? 'Chargable' : 'Ignored';
    }

    /**
     * Get the tax amount to charge on the order.
     */
    public function value($incoming)
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
