<?php

/**
 * @package shop
 */

class VariationFormValidator extends RequiredFields {

	public function php($data) {
		$valid = parent::php($data);

		if($valid && !$this->form->getBuyable($_POST)) {
			$this->validationError(
				"",
				_t('VariationForm.PRODUCT_NOT_AVAILABLE', "This product is not available with the selected options.")
			);

			$valid = false;
		}

		return $valid;
	}

}
