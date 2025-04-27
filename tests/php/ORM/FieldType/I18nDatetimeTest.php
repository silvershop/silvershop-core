<?php

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\I18nDatetime;
use SilverStripe\Dev\SapphireTest;

class I18nDatetimeTest extends SapphireTest
{
    public function testField(): void
    {

        $i18nDatetime = I18nDatetime::create();
        $i18nDatetime->setValue('2012-11-21 11:54:13');

        $i18nDatetime->Nice();
        $i18nDatetime->NiceDate();
        $i18nDatetime->Nice24();

        $this->markTestIncomplete('assertions!');
    }
}
