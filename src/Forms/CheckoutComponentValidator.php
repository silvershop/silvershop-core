<?php

namespace SilverShop\Forms;

use SilverShop\Checkout\CheckoutComponentConfig;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ValidationException;

/**
 * Order validator makes sure everything is set correctly
 * and in place before an order can be placed.
 */
class CheckoutComponentValidator extends RequiredFields
{
    protected $config;

    public function __construct(CheckoutComponentConfig $config)
    {
        $this->config = $config;
        parent::__construct($this->config->getRequiredFields());
    }

    public function php($data)
    {
        $valid = parent::php($data);
        //do component validation
        try {
            $this->config->validateData($data);
        } catch (ValidationException $e) {
            $result = $e->getResult();
            foreach ($result->getMessages() as $message) {
                if (!$this->fieldHasError($message['fieldName'])) {
                    $this->validationError($message['fieldName'], $message['message'], 'bad');
                }
            }
            $valid = false;
        }
        if (!$valid) {
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

    public function fieldHasError($field)
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
