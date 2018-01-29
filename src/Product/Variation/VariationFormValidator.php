<?php

namespace SilverShop\Core\Product\Variation;


use SilverStripe\Forms\RequiredFields;


/**
 * @package shop
 */
class VariationFormValidator extends RequiredFields
{
    public function php($data)
    {
        $valid = parent::php($data);

        if ($valid && !$this->form->getBuyable($_POST)) {
            $this->validationError(
                '',
                _t(
                    'SilverShop\Core\Product\VariationVariationForm.ProductNotAvailable',
                    'This product is not available with the selected options.'
                )
            );

            $valid = false;
        }

        return $valid;
    }
}
