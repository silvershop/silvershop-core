<?php

/**
 * provides I18n formating
 *
 * @package    shop
 * @subpackage i18n
 */
class I18nDatetime extends SS_Datetime
{
    /**
     * Returns the datetime in the format given in the lang file
     * locale sould be set
     */
    public function Nice()
    {
        if ($this->value) {
            return $this->FormatI18N(_t('General.DATETIMEFORMATNICE', '%m/%d/%G %I:%M%p'));
        }
    }

    public function NiceDate()
    {
        if ($this->value) {
            return $this->FormatI18N(_t('General.DATEFORMATNICE', '%m/%d/%G'));
        }
    }

    public function Nice24()
    {
        return date(_t('General.DATETIMEFORMATNICE24', 'd/m/Y H:i'), strtotime($this->value));
    }
}
