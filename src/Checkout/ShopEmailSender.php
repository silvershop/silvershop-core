<?php

declare(strict_types=1);

namespace SilverShop\Checkout;

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Shared "who is this email from" resolution for SilverShop notification senders, so every module resolves the
 * From address and display name the same way instead of copy-pasting the logic.
 *
 * - {@see senderEmail()}: the using class's own `from_email` config, else the store-profile contact email
 *   (silvershop/store-profile, when installed), else the framework admin email, else a no-reply@host fallback.
 * - {@see senderName()}: the store-profile shop name (when installed), else the site title.
 *
 * The optional per-class `from_email` is read via {@see Config::forClass()} so any class can use the trait
 * (service, DataObject or plain utility) without having to be {@see \SilverStripe\Core\Config\Configurable}.
 */
trait ShopEmailSender
{
    /**
     * The From address: explicit `from_email` config, else the store-profile contact email, else the framework
     * admin email, else a generated no-reply address so the mailer never receives an empty From.
     */
    public function senderEmail(): string
    {
        $configured = (string) Config::forClass(static::class)->get('from_email');
        if ($configured !== '') {
            return $configured;
        }

        $siteConfig = SiteConfig::current_site_config();
        if ($siteConfig && $siteConfig->hasField('ContactEmail')) {
            $contact = (string) $siteConfig->getField('ContactEmail');
            if ($contact !== '') {
                return $contact;
            }
        }

        $admin = Email::config()->get('admin_email');
        if (is_array($admin)) {
            $admin = array_key_first($admin) ?: '';
        }

        return $admin ?: ('no-reply@' . (parse_url(Director::absoluteBaseURL(), PHP_URL_HOST) ?: 'example.com'));
    }

    /**
     * The From display name: the store-profile shop name when available, else the site title.
     */
    public function senderName(): string
    {
        $siteConfig = SiteConfig::current_site_config();
        if ($siteConfig && $siteConfig->hasMethod('StoreProfileName')) {
            $name = (string) $siteConfig->StoreProfileName();
            if ($name !== '') {
                return $name;
            }
        }

        return (string) ($siteConfig ? $siteConfig->Title : '');
    }
}
