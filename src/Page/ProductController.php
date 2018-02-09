<?php

namespace SilverShop\Page;

use PageController;
use SilverShop\Forms\AddProductForm;

class ProductController extends PageController
{
    private static $allowed_actions = [
        'Form',
        'AddProductForm',
    ];

    public function Form()
    {
        $form = AddProductForm::create($this, 'Form');
        $this->extend('updateForm', $form);
        return $form;
    }
}
