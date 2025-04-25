<?php

namespace SilverShop\Model\Modifiers\Tax;

use SilverShop\Model\Modifiers\OrderModifier;

/**
 * Base class for creating tax modifiers with.
 *
 * @property double $Rate
 */
class Base extends OrderModifier
{
    private static array $db = [
        'Rate' => 'Double',
    ];

    private static array $defaults = [
        'Rate' => 0.15 //15% tax
    ];

    private static string $singular_name = 'Tax';

    private static string $plural_name = 'Taxes';

    private static string $table_name = 'SilverShop_TaxModifier';

    public function getTableTitle(): string
    {
        $title = parent::getTableTitle();
        if ($this->Rate) {
            $title .= ' ' . _t(
                __CLASS__ . '.AtRate',
                '@ {Rate}%',
                '',
                ['Rate' => number_format($this->Rate * 100, 1)]
            );
        }
        return $title;
    }
}
