<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout;

use SilverShop\Checkout\ShopEmailSender;

/**
 * Minimal consumer of {@link ShopEmailSender} used by {@link ShopEmailSenderTest}.
 */
final class ShopEmailSenderTest_Sender
{
    use ShopEmailSender;

    private static string $from_email = '';
}
