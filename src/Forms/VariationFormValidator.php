<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Forms\Validation\RequiredFieldsValidator;

/**
 * @package shop
 */
class VariationFormValidator extends RequiredFieldsValidator
{
//    public $form;

    public function php($data): bool
    {
        $valid = true;

        if ($valid && !$this->form->getBuyable($_POST)) {
            $this->validationError(
                '',
                _t(
                    'SilverShop\Forms\VariationForm.ProductNotAvailable',
                    'This product is not available with the selected options.'
                )
            );

            $valid = false;
        }

        return $valid;
    }
}
