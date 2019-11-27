<?php

namespace SilverShop\Model\Modifiers\Shipping;

/**
 * Calculates the shipping cost of an order, by taking the products
 * and calculating the shipping weight, based on an array set in _config
 *
 * ASSUMPTION: The total order weight can be at maximum the last item
 * in the $shippingCosts array.
 */
class Weight extends Base
{
    /**
     * Weight to price mapping.
     * Should be an associative array, with the weight as key (KG) and the corresponding price as value.
     * Can be set via Config API, eg.
     * <code>
     * WeightShippingModifier:
     *   weight_cost:
     *     '0.5': 12
     *     '1.0': 15
     *     '2.0': 20
     * </code>
     *
     * @config
     * @var    array
     */
    private static $weight_cost = [];

    protected $weight = 0;

    /**
     * Calculates shipping cost based on Product Weight.
     */
    public function value($subtotal = 0)
    {
        $totalWeight = $this->Weight();
        if (!$totalWeight) {
            return $this->Amount = 0;
        }
        $amount = 0;

        $table = $this->config()->weight_cost;
        if (!empty($table) && is_array($table)) {
            // ensure table is sorted
            ksort($table, SORT_NUMERIC);
            // set the amount to the highest value. In case the weight is higher than the max value in
            // the table, use the highest shipping cost and not 0!
            $amount = end($table);
            reset($table);
            foreach ($table as $weight => $cost) {
                if ($totalWeight <= $weight) {
                    $amount = $cost;
                    break;
                }
            }
        }
        return $this->Amount = $amount;
    }

    public function getTableTitle()
    {
        return _t(
            __CLASS__ . '.TableTitle',
            'Shipping ({Kilograms} kg)',
            '',
            ['Kilograms' => $this->Weight()]
        );
    }

    /**
     * Calculate the total weight of the order
     *
     * @return number
     */
    public function Weight()
    {
        if ($this->weight) {
            return $this->weight;
        }
        $weight = 0;
        $order = $this->Order();
        if ($order && $orderItems = $order->Items()) {
            foreach ($orderItems as $orderItem) {
                if ($product = $orderItem->Product()) {
                    $weight = $weight + ($product->Weight * $orderItem->Quantity);
                }
            }
        }
        return $this->weight = $weight;
    }
}
