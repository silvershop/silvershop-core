<?php

class AddProductForm extends Form{
	
	protected $maxquantity = 20; //populate quantity dropdown with this many values
	
	/**
	 * Fields that can be saved to an order item.
	 */
	protected $saveablefields = array();
	
	function __construct($controller, $name = "AddProductForm"){
		$fields = new FieldList();
		
		if($this->maxquantity){
			$values = array();
			$count = 1;
			while($count <= $this->maxquantity){
				$values[$count] = $count;
				$count++;
			}
			$fields->push(new DropdownField('Quantity','Quantity',$values,1));
		}else{
			$fields->push(new NumericField('Quantity','Quantity',1));
		}
		$actions = new FieldList(
			new FormAction('addtocart',_t("AddProductForm.ADDTOCART",'Add to Cart'))
		);
		$validator = new RequiredFields(array(
			'Quantity'
		));
		parent::__construct($controller,$name,$fields,$actions,$validator);
		$this->addExtraClass("addproductform");
	}
	
	/**
	 * Choose maximum value to populate quantity dropdown
	 */
	function setMaximumQuantity($qty){
		$this->maxquantity = (int)$qty;
	}
	
	function setSaveableFields($fields){
		$this->saveablefields = $fields;
	}
	
	function addtocart($data,$form){
		if($buyable = $this->getBuyable($data)){
			$cart = ShoppingCart::singleton();
			$saveabledata = (!empty($this->saveablefields)) ? Convert::raw2sql(array_intersect_key($data,array_combine($this->saveablefields,$this->saveablefields))) : $data;
			$quantity = isset($data['Quantity']) ? (int) $data['Quantity']: 1;			
			$cart->add($buyable,$quantity,$saveabledata);
			if(!Config::get('ShoppingCart_Controller','direct_to_cart_page')){
				$form->SessionMessage($cart->getMessage(),$cart->getMessageType());
			}
			ShoppingCart_Controller::direct($cart->getMessageType());
		}
	}
	
	function getBuyable($data = null){
		if($this->controller->dataRecord instanceof Buyable){
			return $this->controller->dataRecord;
		}
		return DataObject::get_by_id('Product',(int) $this->request->postVar("BuyableID")); //TODO: get buyable
	}
	
}