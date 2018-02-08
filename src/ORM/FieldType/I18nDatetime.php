<?php

namespace SilverShop\ORM\FieldType;


use SilverStripe\ORM\FieldType\DBDatetime;


/**
 * provides I18n formating
 *
 * @package    shop
 * @subpackage fieldtype
 * @property   DBDatetime $owner
 */
class I18nDatetime extends DBDatetime
{
    /**
     * Returns the datetime in the format given in the lang file
     * locale sould be set
     */
    public function Nice()
    {
        if ($this->value) {
            return strftime(_t('SilverShop\Generic.DateTimeFormatNice', '%m/%d/%G %I:%M%p'), $this->owner->getTimestamp());
        }
    }

    public function NiceDate()
    {
        if ($this->value) {
            return strftime(_t('SilverShop\Generic.DateFormatNice', '%m/%d/%G'), $this->owner->getTimestamp());
        }
    }

    public function Nice24()
    {
        return date(_t('SilverShop\Generic.DateTimeFormatNice24', 'd/m/Y H:i'), $this->owner->getTimestamp());
    }
}
