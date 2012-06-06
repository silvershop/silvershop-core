<?php

class AddProductForm extends Form{
	
	function __construct($controller,$buyable){
		$fields = new FieldSet(
			new NumericField("Quantity",_t("AddProductForm.QUANTITY","Quantity"),1),
			new HiddenField("BuyableID","",$buyable->ID),
			new HiddenField("BuyableClass","",$buyable->ClassName),
			new LiteralField("Price", $buyable->renderWith("PriceTag"))
		);
		$actions = new FieldSet(
			new FormAction('addtocart',_t("AddProductForm.ADDTOCART",'Add to Cart'))
		);
		$validator = new RequiredFields(array(
			'Quantity',
			'BuyableID'
		));
		parent::__construct($controller,"AddProductForm",$fields,$actions,$validator);
	}
	
	function addtocart($data,$form){
		if($buyable = $this->getBuyable()){
			$cart = ShoppingCart::getInstance();
			$quantity = isset($data['Quantity']) ? (int) $data['Quantity']: 1;
			$cart->add($buyable,$quantity,$data);
			if(!ShoppingCart_Controller::get_direct_to_cart()){
				$form->SessionMessage($cart->getMessage(),$cart->getMessageType());
			}
			ShoppingCart_Controller::direct($cart->getMessageType());
		}
	}
	
	protected function getBuyable(){
		if($this->controller->dataRecord instanceof Buyable){
			return $this->controller->dataRecord;
		}
		return DataObject::get_by_id('Product',(int) $this->request->postVar("BuyableID")); //TODO: get buyable
	}
	
}