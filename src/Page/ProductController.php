<?php

declare(strict_types=1);

namespace SilverShop\Page;

use PageController;
use SilverShop\Forms\AddProductForm;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\SecurityToken;
use SilverStripe\View\Requirements;

class ProductController extends PageController
{
    private static array $allowed_actions = [
        'Form',
        'AddProductForm',
    ];

    protected function init()
    {
        parent::init();

        $record = $this->data();
        if ($record instanceof Product && $record->Variations()->exists()) {
            if (SecurityToken::is_enabled()) {
                SecurityToken::inst()->getValue();
            }
            Requirements::javascript('silvershop/core:client/dist/javascript/VariationsTable.js');
        }
    }

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
