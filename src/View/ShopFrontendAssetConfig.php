<?php

declare(strict_types=1);

namespace SilverShop\View;

use SilverStripe\Core\Config\Configurable;

/**
 * Configuration for storefront-facing CSS bundled with this module.
 */
class ShopFrontendAssetConfig
{
    use Configurable;

    /**
     * When false, built-in Layout templates skip `<% require css(...) %>` for shop frontend CSS.
     * JavaScript for cart/checkout behaviour is still included where forms require it.
     *
     * @config
     */
    private static bool $include_default_styles = true;
}
