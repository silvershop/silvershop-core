<?php

namespace SilverShop\Tests\ORM\FieldType;

use SilverShop\ORM\FieldType\I18nDatetime;
use SilverStripe\Dev\SapphireTest;

class I18nDatetimeTest extends SapphireTest
{
    public function testField(): void
    {

        $field = I18nDatetime::create();
        $field->setValue('2012-11-21 11:54:13');

        $field->Nice();
        $field->NiceDate();
        $field->Nice24();

        $this->markTestIncomplete('assertions!');
    }
}
