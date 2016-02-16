<?php

/**
 * Base class for creating tax modifiers with.
 */
class TaxModifier extends OrderModifier
{
    private static $db            = array(
        'Rate' => 'Double',
    );

    private static $defaults      = array(
        'Rate' => 0.15 //15% tax
    );

    private static $singular_name = "Tax";

    private static $plural_name   = "Taxes";

    public function TableTitle()
    {
        $title = parent::TableTitle();
        if ($this->Rate) {
            $title .= " " . sprintf(_t("TaxModifier.ATRATE", "@ %s"), number_format($this->Rate * 100, 1) . "%");
        }
        return $title;
    }
}
