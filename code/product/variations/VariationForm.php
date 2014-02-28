<?php

class VariationForm extends AddProductForm{

	public static $include_json = true;

	public function __construct($controller, $name = "VariationForm"){
		parent::__construct($controller,$name);
		$product = $controller->data();
		$farray = array();
		$requiredfields = array();
		$attributes = $product->VariationAttributeTypes();
		foreach($attributes as $attribute){
			$farray[] = $attribute->getDropDownField("choose $attribute->Label ...",$product->possibleValuesForAttributeType($attribute));
			$requiredfields[] = "ProductAttributes[$attribute->ID]";
		}
		$fields = new FieldList($farray);

		if(self::$include_json){ //TODO: this should be included as js validation instead
			$vararray = array();
			if($vars = $product->Variations()){
				foreach($vars as $var){
					$vararray[$var->ID] = $var->AttributeValues()->map('ID','ID');
				}
			}
			$fields->push(new HiddenField('VariationOptions','VariationOptions',json_encode($vararray)));
		}
		$fields->merge($this->Fields());
		$this->setFields($fields);
		$requiredfields[] = 'Quantity';
		$this->setValidator(new VariationFormValidator($requiredfields));
		$this->extend('updateVariationForm');
	}

	public function addtocart($data,$form){
		if($variation = $this->getBuyable($data)){
			$quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int) $data['Quantity'] : 1;
			$cart = ShoppingCart::singleton();
			if($cart->add($variation,$quantity)){
				$form->sessionMessage(sprintf("Successfully added to cart. <a href='%s'>Check out now</a>", CheckoutPage::get()->first()->Link()) ,"good");
			}else{
				$form->sessionMessage($cart->getMessage(),$cart->getMessageType());
			}
		}else{
			$form->sessionMessage("That variation is not available, sorry.","bad"); //validation fail
		}
		ShoppingCart_Controller::direct();
	}

	public function getBuyable($data = null){
		if(isset($data['ProductAttributes']) && $variation = $this->Controller()->getVariationByAttributes($data['ProductAttributes'])){
			return $variation;
		}
		return null;
	}

}

class VariationFormValidator extends RequiredFields{

	public function php($data){
		$valid = parent::php($data);
		if($valid && !$this->form->getBuyable($_POST)){
			$this->validationError(
				"","This product is not available with the selected options."
			);
			$valid = false;
		}
		return $valid;
	}

}
