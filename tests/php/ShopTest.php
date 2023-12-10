<?php

namespace SilverShop\Tests;

use SilverStripe\Core\Environment;

/**
 * Helper class for setting up shop tests
 *
 * @package    shop
 * @subpackage tests
 */
class ShopTest
{
    public static function setConfiguration()
    {
        include __DIR__ . DIRECTORY_SEPARATOR . 'test_config.php';

        Environment::putEnv('SS_SEND_ALL_EMAILS_TO', '');
    }
}
