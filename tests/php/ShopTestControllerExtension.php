<?php

namespace SilverShop\Tests;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Test Extension that can be applied to controllers to test if a requested page returns the desired page-class.
 */
class ShopTestControllerExtension extends Extension implements TestOnly
{
    public function onAfterInit()
    {
        $this->owner->response->addHeader(
            'X-TestPageClass',
            get_class($this->owner)
        );
        $params = $this->owner->getURLParams();
        if (isset($params['Action'])) {
            $this->owner->response->addHeader('X-TestPageAction', $params['Action']);
        }
    }
}
