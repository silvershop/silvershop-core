<?php

declare(strict_types=1);

namespace SilverShop\Tests;

use SilverStripe\Core\Environment;
use SilverStripe\View\SSViewer;

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

    /**
     * Render pages with the bundled shoptest theme, so functional tests that assert on page
     * markup don't depend on whichever theme (if any) the host project provides.
     * Call from setUp(); SapphireTest resets config after each test.
     */
    public static function useShopTestTheme(): void
    {
        $themeDir = substr((string) realpath(__DIR__ . '/themes/shoptest'), strlen(BASE_PATH));

        SSViewer::config()->set('theme_enabled', true);
        SSViewer::set_themes([$themeDir, '$default']);
    }
}
