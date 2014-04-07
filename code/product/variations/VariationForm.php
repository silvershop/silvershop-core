<?php

/**
 * @package shop
 */
class VariationForm extends AddProductForm {

	public static $include_json = true;

	public function __construct($controller, $name = "VariationForm") {
		parent::__construct($controller,$name);

		$product = $controller->data();
		$farray = array();
		$requiredfields = array();
		$attributes = $product->VariationAttributeTypes();

		foreach($attributes as $attribute){
			$farray[] = $attribute->getDropDownField(
				"Choose $attribute->Label ...", 
				$product->possibleValuesForAttributeType($attribute)
			);
			
			$requiredfields[] = "ProductAttributes[$attribute->ID]";
		}
		
		$fields = new FieldList($farray);

		if(self::$include_json) {
			$vararray = array();

			if($vars = $product->Variations()) {
				foreach($vars as $var) {
					$vararray[$var->ID] = $var->AttributeValues()->map('ID','ID');
				}
			}

			$fields->push(new HiddenField('VariationOptions','VariationOptions',
				json_encode($vararray)
			));
		}

		$fields->merge($this->Fields());

		$this->setFields($fields);
		$requiredfields[] = 'Quantity';

		$this->setValidator(new VariationFormValidator(
			$requiredfields
		));

		$this->extend('updateVariationForm');
	}

	/**
	 * Adds a given product to the cart. If a hidden field is passed 
	 * (ValidateVariant) then simply a validation of the user including that
	 * product is done and the users cart isn't actually changed.
	 *
	 * @return mixed
	 */
	public function addtocart($data,$form) {
		if($variation = $this->getBuyable($data)) {
			$quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int) $data['Quantity'] : 1;
			$cart = ShoppingCart::singleton();
			
			// if we are in just doing a validation step then check
			if($this->request->requestVar('ValidateVariant')) {
				$message = '';
				$success = false;

				try {
					$success = $variation->canPurchase(null, $data['Quantity']);
				} catch(ShopBuyableException $e) {
					$message = get_class($e);
					// added hook to update message
					$this->extend('updateVariationAddToCartMessage', $e, $message, $variation);
				}

				$ret = array(
					'Message' => $message,
					'Success' => $success,
					'Price' => $variation->dbObject('Price')->TrimCents()
				);

				$this->extend('updateVariationAddToCartAjax', $ret, $variation, $form);

				return json_encode($ret);

			}

			if($cart->add($variation, $quantity)) {
				$form->sessionMessage(
					"Successfully added to cart.",
					"good"
				);
			} else {
				$form->sessionMessage($cart->getMessage(),$cart->getMessageType());
			}
		} else {
			$variation = null;
			$form->sessionMessage("That variation is not available, sorry.","bad"); //validation fail
		}

		$this->extend('updateVariationAddToCart', $form, $variation);

		$request = $this->getRequest();
		$this->extend('updateVariationFormResponse', $request, $response, $variation, $quantity, $form);
		return $response ? $response : ShoppingCart_Controller::direct();
	}

	public function getBuyable($data = null) {
		if(isset($data['ProductAttributes']) && $variation = $this->Controller()->getVariationByAttributes($data['ProductAttributes'])){
			return $variation;
		}

		return null;
	}

}
