<?php

namespace SilverShop\ORM\FieldType;

use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * Provides i18n formatting
 */
class I18nDatetime extends DBDatetime
{
    /**
     * Returns the datetime in the format given in the lang file
     * 'SilverShop\Generic.DateTimeFormatNice'. Defaults to '%m/%d/%G %I:%M%p'
     *
     * @return string|null
     */
    public function Nice()
    {
        if ($this->value) {
            return strftime(_t('SilverShop\Generic.DateTimeFormatNice', '%m/%d/%G %I:%M%p'), $this->getTimestamp());
        }
    }

    /**
     * Returns the date in the format given in the lang file.
     * 'SilverShop\Generic.DateFormatNice'. Defaults to '%m/%d/%G'
     *
     * @return string|null
     */
    public function NiceDate()
    {
        if ($this->value) {
            return strftime(_t('SilverShop\Generic.DateFormatNice', '%m/%d/%G'), $this->getTimestamp());
        }
    }

    /**
     * Returns the 24h datetime in the format given in the lang file.
     * 'SilverShop\Generic.DateTimeFormatNice24'. Defaults to 'd/m/Y H:i'
     *
     * @return string|null
     */
    public function Nice24()
    {
        return date(_t('SilverShop\Generic.DateTimeFormatNice24', 'd/m/Y H:i'), $this->getTimestamp());
    }
}
