<?php

namespace SilverShop\Page;

use PageController;
use SilverStripe\Core\Injector\Injector;

/**
 * @extends PageController<Product>
 */
class ProductController extends PageController
{
    private static array $allowed_actions = [
        'Form',
        'AddProductForm',
    ];

    public function Form()
    {
        $form = Injector::inst()->create($this->data()->getFormClass(), $this, 'Form');
        $this->extend('updateForm', $form);
        return $form;
    }
}
