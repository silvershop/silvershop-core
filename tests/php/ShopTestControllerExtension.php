<?php

declare(strict_types=1);

namespace SilverShop\Tests;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Test Extension that can be applied to controllers to test if a requested page returns the desired page-class.
 * @extends Extension<static>
 */
class ShopTestControllerExtension extends Extension implements TestOnly
{
    public $response;

    public function onAfterInit(): void
    {
        $this->getOwner()->response->addHeader(
            'X-TestPageClass',
            get_class($this->getOwner())
        );
        $params = $this->getOwner()->getURLParams();
        if (isset($params['Action'])) {
            $this->getOwner()->response->addHeader('X-TestPageAction', $params['Action']);
        }
    }
}
