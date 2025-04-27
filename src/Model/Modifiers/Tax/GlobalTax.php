<?php

namespace SilverShop\Model\Modifiers\Tax;

use SilverShop\Model\Order;

/**
 * Handles calculation of sales tax on Orders on
 * a per-country basis.
 *
 * @property ?string $Country
 */
class GlobalTax extends Base
{
    private static array $db = [
        'Country' => 'Varchar',
    ];

    /**
     * Tax rates per country
     */
    private static array $country_rates = [];

    private static string $table_name = 'SilverShop_GlobalTaxModifier';

    public function value($incoming): int|float
    {
        $rate = $this->Type == 'Chargable'
            ?
            $this->Rate()
            :
            round(1 - (1 / (1 + $this->Rate())), Order::config()->rounding_precision);
        return $incoming * $rate;
    }

    public function Rate()
    {
        // If the order is no longer in cart, rely on the saved data
        if ($this->OrderID && $this->Order()->exists() && !$this->Order()->IsCart()) {
            return $this->getField('Rate');
        }

        $rates = self::config()->country_rates;
        $country = $this->Country();
        if ($country && isset($rates[$country])) {
            return $this->Rate = $rates[$country]['rate'];
        }
        $defaults = self::config()->defaults;
        return $this->Rate = $defaults['Rate'];
    }

    public function getTableTitle(): string
    {
        $country = $this->Country() ? ' (' . $this->Country() . ') ' : '';

        return parent::getTableTitle() . $country .
            ($this->Type == 'Chargable' ? '' : _t(__CLASS__ . '.Included', ' (included in the above price)'));
    }

    public function Country()
    {
        if ($this->OrderID && $this->Order()->exists() && $address = $this->Order()->getBillingAddress()) {
            return $address->Country;
        }

        return null;
    }

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        // While the order is still in "Cart" status, persist country code to DB
        if ($this->OrderID && $this->Order()->exists() && $this->Order()->IsCart()) {
            $this->setField('Country', $this->Country());
        }
    }
}
