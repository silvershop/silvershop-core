<?php

namespace SilverShop\ORM;

use SilverStripe\ORM\HasManyList;

/**
 * Additional functions for Item lists.
 */
class OrderItemList extends HasManyList
{
    public function Quantity(): float
    {
        return $this->Sum('Quantity');
    }

    public function Plural(): bool
    {
        return $this->Quantity() > 1;
    }

    /**
     * Sums up all of desired field for items, and multiply by quantity.
     * Optionally sum product field instead.
     *
     * @param string  $field     - field to sum
     * @param boolean $onproduct - sum from product or not
     *
     * @return float sum total of field
     */
    public function Sum($field, $onproduct = false): float
    {
        $total = 0;
        foreach ($this->getIterator() as $item) {
            $quantity = ($field === 'Quantity') ? 1 : $item->Quantity;
            if (!$onproduct) {
                $total += $item->$field * $quantity;
            } elseif ($item->hasMethod($field)) {
                $total += $item->$field() * $quantity;
            } elseif ($product = $item->Product()) {
                $total += $product->$field * $quantity;
            }
        }
        return $total;
    }

    /**
     * Add up the totals of all the order items in this list.
     */
    public function SubTotal(): int|float
    {
        $result = 0;
        foreach ($this->getIterator() as $item) {
            $result += $item->Total();
        }

        return $result;
    }
}
