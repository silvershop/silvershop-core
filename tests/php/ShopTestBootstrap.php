<?php

declare(strict_types=1);

namespace SilverShop\Tests;

use SilverStripe\Core\Environment;

/**
 * Helper for setting up shop tests (not a PHPUnit test case).
 */
final class ShopTestBootstrap
{
    public static function setConfiguration(): void
    {
        include __DIR__ . DIRECTORY_SEPARATOR . 'test_config.php';

        Environment::setEnv('SS_SEND_ALL_EMAILS_TO', '');
    }
}
