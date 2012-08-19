<?php

class VariationForm extends Form{
	
	static $max_quantity = 50; //populate quantity dropdown with this many values
	
	function __construct($controller, $name = "VariationForm"){
		$product = $controller->data();
		$farray = array();
		$requiredfields = array();
		$attributes = $product->VariationAttributeTypes();
		foreach($attributes as $attribute){
			$farray[] = $attribute->getDropDownField("choose $attribute->Label ...",$product->possibleValuesForAttributeType($attribute));
			$requiredfields[] = "ProductAttributes[$attribute->ID]";
		}
		$fields = new FieldSet($farray);
		if($maxquantity = self::$max_quantity){
			$values = array();
			$count = 1;
			while($count <= $maxquantity){
				$values[$count] = $count;
				$count++;
			}
			$fields->push(new DropdownField('Quantity','Quantity',$values,1));
		}else{
			$fields->push(new NumericField('Quantity','Quantity',1));
		}
		if(true){
			//TODO: make javascript json inclusion optional
			$vararray = array();
			if($vars = $product->Variations()){
				foreach($vars as $var){
					$vararray[$var->ID] = $var->AttributeValues()->map('ID','ID');
				}
			}
			$fields->push(new HiddenField('VariationOptions','VariationOptions',json_encode($vararray)));
		}
		$actions = new FieldSet(
			new FormAction('addtocart', _t("Product.ADDLINK","Add this item to cart"))
		);
		$requiredfields[] = 'Quantity';
		$validator = new RequiredFields($requiredfields);
	
		parent::__construct($controller,$name,$fields,$actions,$validator);
		$this->extend('updateForm');
	}
	
	function addtocart($data,$form){
		if(isset($data['ProductAttributes']) && $variation = $this->Controller()->getVariationByAttributes($data['ProductAttributes'])){
			$quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int) $data['Quantity'] : 1;
			$cart = ShoppingCart::singleton();
			if($cart->add($variation,$quantity)){
				$form->sessionMessage("Successfully added to cart.","good");
			}else{
				$form->sessionMessage($cart->getMessage(),$cart->getMessageType());
			}
				
		}else{
			$form->sessionMessage("That variation is not available, sorry.","bad"); //validation fail
		}
		ShoppingCart_Controller::direct();
	}
	
}