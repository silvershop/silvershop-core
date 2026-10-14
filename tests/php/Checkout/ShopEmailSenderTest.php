<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Covers the shared {@link \SilverShop\Checkout\ShopEmailSender} sender-identity resolution (core behaviour,
 * i.e. without silvershop/store-profile installed).
 */
final class ShopEmailSenderTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testSenderNameFallsBackToSiteTitle(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->Title = 'Acme Store';
        $siteConfig->write();

        $sender = new ShopEmailSenderTest_Sender();
        $this->assertSame('Acme Store', $sender->senderName());
    }

    public function testSenderEmailPrefersConfiguredFromEmail(): void
    {
        Config::modify()->set(ShopEmailSenderTest_Sender::class, 'from_email', 'hello@example.com');

        $sender = new ShopEmailSenderTest_Sender();
        $this->assertSame('hello@example.com', $sender->senderEmail());
    }

    public function testSenderEmailFallsBackToAdminEmail(): void
    {
        Config::modify()->set(Email::class, 'admin_email', 'admin@example.com');

        $sender = new ShopEmailSenderTest_Sender();
        $this->assertSame('admin@example.com', $sender->senderEmail());
    }
}
