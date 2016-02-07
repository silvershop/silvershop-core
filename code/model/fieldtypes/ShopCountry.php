<?php

class ShopCountry extends Varchar
{
    public function __construct($name, $size = 3, $options = array())
    {
        parent::__construct($name, $size = 3, $options);
    }

    public function forTemplate()
    {
        return $this->Nice();
    }

    /**
     * Convert ISO abbreviation to full, translated country name
     */
    public function Nice()
    {
        $val = ShopConfig::countryCode2name($this->value);
        if (!$val) {
            $val = $this->value;
        }
        return _t("ShopCountry." . $this->value, $val);
    }

    public function XML()
    {
        return Convert::raw2xml($this->Nice());
    }
}
