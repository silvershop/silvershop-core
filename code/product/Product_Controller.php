<?php

class Product_Controller extends PageController
{
    private static $allowed_actions = array(
        'Form',
        'AddProductForm',
    );

    public $formclass       = "AddProductForm"; //allow overriding the type of form used

    public function Form()
    {
        $formclass = $this->formclass;
        $form = new $formclass($this, "Form");
        $this->extend('updateForm', $form);
        return $form;
    }
}
