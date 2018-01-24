<?php

/**
 * Handles calculation of sales tax on Orders on
 * a per-country basis.
 *
 * @package    shop
 * @subpackage modifiers
 */
class GlobalTaxModifier extends TaxModifier
{
    private static $db            = array(
        'Country' => 'Varchar',
    );

    private static $country_rates = array();

    public function value($incoming)
    {
        $rate = $this->Type == "Chargable"
            ?
            $this->Rate()
            :
            round(1 - (1 / (1 + $this->Rate())), Order::config()->rounding_precision);
        return $incoming * $rate;
    }

    public function Rate()
    {
        // If the order is no longer in cart, rely on the saved data
        if ($this->OrderID && !$this->Order()->IsCart()){
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

    public function TableTitle()
    {
        $country = $this->Country() ? ' (' . $this->Country() . ') ' : '';
        return parent::TableTitle() . $country .
            ($this->Type == "Chargable" ? '' : _t("GlobalTaxModifier.Included", ' (included in the above price)'));
    }

    public function Country()
    {
        if ($this->OrderID && $address = $this->Order()->getBillingAddress()) {
            return $address->Country;
        }

        return null;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // While the order is still in "Cart" status, persist country code to DB
        if ($this->OrderID && $this->Order()->IsCart()){
            $this->setField('Country', $this->Country());
        }
    }
}
