<?php

class AddProductForm extends Form{
	
	function __construct($controller,$product){
		
		$fields = new FieldSet(
			new NumericField("Quantity",_t("AddProductForm.QUANTITY","Quantity"),1),
			new HiddenField("ProductID","",$product->ID),
			new LiteralField("Price", "<h2 class=\"price\">".$product->dbObject('Price')->Nice()."</h2>")
		);
		$actions = new FieldSet(
			new FormAction('addtocart',_t("AddProductForm.ADDTOCART",'Add to Cart'))
		);
		
		$validator = new RequiredFields(array(
			'Quantity',
			'ProductID'
		));
		
		parent::__construct($controller,"AddProductForm",$fields,$actions,$validator);
	}
	
	function addtocart($data,$form){
		$product = DataObject::get_by_id('Product',(int) $data['ProductID']);
		$cart = ShoppingCart::getInstance();
		$cart->add($product,(int) $data['Quantity'],$data);
		$form->SessionMessage($cart->getMessage(),$cart->getMessageType());
		ShoppingCart_Controller::direct($cart->getMessageType());
	}
	
}