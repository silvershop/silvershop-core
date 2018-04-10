<?php

namespace SilverShop\Page;

use PageController;
use SilverShop\Forms\AddProductForm;
use SilverStripe\Core\Injector\Injector;

class ProductController extends PageController
{
    private static $allowed_actions = [
        'Form',
        'AddProductForm',
    ];

    public function Form()
    {
        $form = Injector::inst()->create($this->getFormClass(), $this, 'Form');
        $this->extend('updateForm', $form);
        return $form;
    }
}
