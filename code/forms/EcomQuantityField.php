<?php

/**
 * A links-based field for increasing, decreasing and setting a order item quantity
 */

class EcomQuantityField extends ViewableData{
	
	protected $item = null;
	protected $parameters = null;
	protected $classes = array('ajaxQuantityField');
	protected $template = 	'EcomQuantityField';
		
	function __construct($object, $parameters = null){
		
		if($object instanceof Product){
			$this->item = ShoppingCart::get_item_by_id($object->ID,null,$parameters);
			 //provide a 0-quantity facade item if there is no such item in cart
			if(!$this->item) $this->item = new Product_OrderItem($object,0);
			
			//TODO: perhaps we should just store the product itself, and do away with the facade, as it might be unnecessary complication
		}elseif($object instanceof OrderItem){
			$this->item = $object;
		}
		
		if(!$this->item)
			user_error("EcomQuantityField: no item or product passed to constructor.");

		$this->parameters = $parameters;
		//TODO: include javascript for easy update
	}
	
	function setClasses($newclasses, $overwrite = false){
		if($overwrite)
			$this->classes = array_merge($this->classes,$newclasses);
		else
			$this->classes = $newclasses;
	}
	
	function setTemplate($template){
		$this->template = $template;
	}
	
	function Item(){
		return $this->item;
	}
	
	function Field() {
		$size = 3; //make these customisable
		$maxlength = 3;

		$attributes = array(
			'type' => 'text',
			'class' => implode(' ',$this->classes),
			'name' => $this->item->MainID() . '_Quantity',
			'value' => ($this->item->Quantity) ? $this->item->Quantity : "",
			'maxlength' => $maxlength,
			'size' => $size 
		);
		
		//IMPROVE ME: hack to use the form field createTag method ...perhaps this should become a form field instead
		$formfield = new FormField('hack'); 
		return $formfield->createTag('input', $attributes);
	}
	
	/**
	 * Used for storing the quantity update link for ajax use.
	 */
	function AJAXLinkHiddenField(){
		if($quantitylink = ShoppingCart::set_quantity_item_link($this->item->getProductIDForSerialization(), null,$this->parameters)){
			$attributes = array(
				'type' => 'hidden',
				'class' => 'ajaxQuantityField_qtylink',
				'name' => $this->item->MainID() . '_Quantity_SetQuantityLink',
				'value' => $quantitylink
			);
			$formfield = new FormField('hack'); 
			return $formfield->createTag('input', $attributes);
		}
	}
	
	function IncrementLink(){
		$varid = ($this->item instanceof ProductVariation_OrderItem) ? $this->item->ProductVariationID : null;
		return ShoppingCart::add_item_link($this->item->getProductIDForSerialization(), $varid,$this->parameters);
	}
	
	function DecrementLink(){
		$varid = ($this->item instanceof ProductVariation_OrderItem) ? $this->item->ProductVariationID : null;
		return ShoppingCart::remove_item_link($this->item->getProductIDForSerialization(), $varid,$this->parameters);
	}
	
	

	function forTemplate(){
		return $this->renderWith($this->template);		
	}
	
}