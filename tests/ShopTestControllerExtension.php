<?php

/**
 * Test Extension that can be applied to controllers to test if a requested page returns the desired page-class.
 */
class ShopTestControllerExtension extends Extension
{
    public function onAfterInit()
    {
        $this->owner->response->addHeader(
            'X-TestPageClass',
            $this->owner instanceof ContentController
                ? $this->owner->ClassName
                : $this->owner->class
        );
        $params = $this->owner->getURLParams();
        if (isset($params['Action'])) {
            $this->owner->response->addHeader('X-TestPageAction', $params['Action']);
        }
    }
}
