<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverShop\Checkout\CheckoutComponentConfig;

/**
 * Order validator makes sure everything is set correctly
 * and in place before an order can be placed.
 */
class CheckoutComponentValidator extends RequiredFieldsValidator
{
//    protected Form $form;

    protected CheckoutComponentConfig $config;

    public function __construct(CheckoutComponentConfig $checkoutComponentConfig)
    {
        $this->config = $checkoutComponentConfig;
    }

    public function php($data): bool
    {
        $valid = true;
        //do component validation
        try {
            $this->config->validateData($data);
        } catch (ValidationException $validationException) {
            $result = $validationException->getResult();
            foreach ($result->getMessages() as $message) {
                if (!$this->fieldHasError($message['fieldName'])) {
                    $this->validationError($message['fieldName'], $message['message'], 'bad');
                }
            }

            $valid = false;
        }

        if(!$valid) {
            $this->form->sessionMessage(
                _t(
                    __CLASS__ . ".InvalidDataMessage",
                    "There are problems with the data you entered. See below:"
                ),
                "bad"
            );
        }

        return $valid;
    }

    public function fieldHasError($field): bool
    {
        if ($this->getErrors()) {
            foreach ($this->getErrors() as $error) {
                if ($error['fieldName'] === $field) {
                    return true;
                }
            }
        }

        return false;
    }
}
