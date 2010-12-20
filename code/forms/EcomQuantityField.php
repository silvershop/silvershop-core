<?php

/**
 * A links-based field for increasing, decreasing and setting a order item quantity
 */

class EcomQuantityField extends NumericField{

	protected $item = null;

	protected $parameters = null;

	protected $classes = array('ajaxQuantityField');

	protected $template = 'EcomQuantityField';

	function __construct($object, $parameters = null){
		if($object instanceof OrderItem){
			$this->item = $object;
		}
		elseif($object instanceof DataObject){
			if($object->ClassName == "OrderItem") {
				$className = "OrderItem";
			}
			else {
				$className = $object->ClassName.EcommerceItemDecorator::get_order_item_class_name_post_fix();
			}
			$this->item = ShoppingCart::get_item_by_id($object->ID, $className, $parameters);
			//provide a 0-quantity facade item if there is no such item in cart
			if(!$this->item) {
				if(class_exists($orderItem)) {
					$this->item = new $className();
					$this->item->addItem($object, 0);
				}
				else {
					user_error("EcomQuantityField: $className does not exist - check code.", E_USER_ERROR);
				}
			}
		}
		else {
			user_error("EcomQuantityField: $object could not be added.", E_USER_ERROR);
		}
		if(!$this->item) {
			user_error("EcomQuantityField: no item or product passed to constructor.", E_USER_ERROR);
		}
		$this->parameters = $parameters;
		//TODO: include javascript for easy update
	}

	function setClasses($newclasses, $overwrite = false){
		if($overwrite) {
			$this->classes = array_merge($this->classes,$newclasses);
		}
		else {
			$this->classes = $newclasses;
		}
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
		return $this->createTag('input', $attributes);
	}

	/**
	 * Used for storing the quantity update link for ajax use.
	 */
	function AJAXLinkHiddenField(){
		if($quantitylink = ShoppingCart::set_quantity_item_link($this->item->getProductIDForSerialization(), $this->item->ClassName,$this->parameters)){
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
		return Convert::raw2att(ShoppingCart::add_item_link($this->item->getProductIDForSerialization(), $this->item->ClassName,$this->parameters));
	}

	function DecrementLink(){
		return Convert::raw2att(ShoppingCart::remove_item_link($this->item->getProductIDForSerialization(), $this->item->ClassName,$this->parameters));
	}

	function forTemplate(){
		return $this->renderWith($this->template);
	}

}
