<?php

declare(strict_types=1);

namespace SilverShop\ORM\FieldType;

use SilverStripe\ORM\FieldType\DBCurrency;

/**
 * Allows casting some template values to show "FREE" instead of $0.00.
 */
class CanBeFreeCurrency extends DBCurrency
{
    public function Nice(): string
    {
        if ($this->value == 0) {
            return _t('SilverShop\ORM\FieldType\ShopCurrency.Free', '<span class="free">FREE</span>');
        }

        return parent::Nice();
    }
}
