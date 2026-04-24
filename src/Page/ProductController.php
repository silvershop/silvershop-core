<?php

declare(strict_types=1);

namespace SilverShop\Page;

use PageController;
use SilverStripe\Core\Injector\Injector;
use SilverShop\Forms\AddProductForm;

class ProductController extends PageController
{
    private static array $allowed_actions = [
        'Form',
        'AddProductForm',
    ];

    public function Form()
    {
        $form = Injector::inst()->create($this->getFormClass(), $this, 'Form');
        $this->data()->extend('updateForm', $form);
        return $form;
    }

    /**
     * Get the form class to use to edit this product in the frontend
     * @return string FQCN
     */
    public function getFormClass(): string
    {
        $formClass = AddProductForm::class;
        $this->data()->extend('updateFormClass', $formClass);
        return $formClass;
    }
}
