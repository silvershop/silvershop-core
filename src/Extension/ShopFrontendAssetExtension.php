<?php

declare(strict_types=1);

namespace SilverShop\Extension;

use SilverShop\View\ShopFrontendAssetConfig;
use SilverStripe\Core\Extension;

/**
 * Exposes {@see ShopFrontendAssetConfig} to templates as `$SilverShopIncludeDefaultStyles`.
 */
class ShopFrontendAssetExtension extends Extension
{
    /**
     * @internal Templates resolve `$SilverShopIncludeDefaultStyles` via this getter.
     */
    public function getSilverShopIncludeDefaultStyles(): bool
    {
        return (bool) ShopFrontendAssetConfig::config()->get('include_default_styles');
    }
}
