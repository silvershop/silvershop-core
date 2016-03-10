<?php

/**
 * Handles calculation of sales tax on Orders.
 *
 * @package    shop
 * @subpackage modifiers
 */
class FlatTaxModifier extends TaxModifier
{
    private static $name            = "GST";

    private static $rate            = 0.15;

    private static $exclusive       = true;

    private static $includedmessage = "%.1f%% %s (inclusive)";

    private static $excludedmessage = "%.1f%% %s";

    public function populateDefaults()
    {
        parent::populateDefaults();
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
