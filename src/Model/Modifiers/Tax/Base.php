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
    private static $db = [
        'Rate' => 'Double',
    ];

    private static $defaults = [
        'Rate' => 0.15 //15% tax
    ];

    private static $table_name = 'SilverShop_TaxModifier';

    private static $singular_name = 'Tax';

    private static $plural_name = 'Taxes';

    public function getTableTitle()
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
