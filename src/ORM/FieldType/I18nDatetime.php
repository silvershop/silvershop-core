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
     * 'SilverShop\Generic.DateTimeFormatNice'. Defaults to 'm/d/Y h:i A'
     */
    public function Nice(): ?string
    {
        if (!$this->value) {
            return null;
        }
        return date(
            _t('SilverShop\Generic.DateTimeFormatNice', 'm/d/Y h:i A'),
            $this->getTimestamp()
        );
    }

    /**
     * Returns the date in the format given in the lang file.
     * 'SilverShop\Generic.DateFormatNice'. Defaults to 'm/d/Y'
     */
    public function NiceDate(): ?string
    {
        if (!$this->value) {
            return null;
        }
        return date(
            _t('SilverShop\Generic.DateFormatNice', 'm/d/Y'),
            $this->getTimestamp()
        );
    }

    /**
     * Returns the 24h datetime in the format given in the lang file.
     * 'SilverShop\Generic.DateTimeFormatNice24'. Defaults to 'd/m/Y H:i'
     */
    public function Nice24(): ?string
    {
        if (!$this->value) {
            return null;
        }
        return date(
            _t('SilverShop\Generic.DateTimeFormatNice24', 'd/m/Y H:i'),
            $this->getTimestamp()
        );
    }
}
