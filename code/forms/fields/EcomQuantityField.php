<?php


/**
 * @Description: A links-based field for increasing, decreasing and setting a order item quantity
 *
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class EcomQuantityField extends NumericField {

	protected static $hide_plus_and_minus = false;
		static function get_hide_plus_and_minus(){return self::$hide_plus_and_minus;}
		static function set_hide_plus_and_minus($v){self::$hide_plus_and_minus = $v;}

	protected $item = null;
	protected $parameters = null;
	protected $classes = array('ajaxQuantityField');
	protected $template = 'EcomQuantityField';

	function __construct($object, $parameters = null){
		Requirements::javascript("ecommerce/javascript/EcomQuantityField.js");
		Requirements::customScript("EcomQuantityField.set_hidePlusAndMins(".(EcomQuantityField::get_hide_plus_and_minus() ? 1 : 0).")");
		if(Object::has_extension($object->class,'Buyable')){
			$this->item = ShoppingCart::get_order_item_by_buyableid($object->ID,$object->ClassName.Buyable::get_order_item_class_name_post_fix(),$parameters);
			 //provide a 0-quantity facade item if there is no such item in cart
			if(!$this->item) {
				$this->item = new Product_OrderItem($object,0);
			}
			//TODO: perhaps we should just store the product itself, and do away with the facade, as it might be unnecessary complication
		}
		elseif($object instanceof OrderItem && $object->BuyableID){
			$this->item = $object;
		}
		else{
			user_error("EcomQuantityField: no/bad order item or buyable passed to constructor.", E_USER_WARNING);
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
		$formfield = new FormField('hack');
		return $formfield->createTag('input', $attributes);
	}

	/**
	 * Used for storing the quantity update link for ajax use.
	 */
	function AJAXLinkHiddenField(){
		if($quantitylink = ShoppingCart::set_quantity_item_link($this->item->BuyableID, $this->item->class,$this->parameters)){
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
		return Convert::raw2att(ShoppingCart::increment_item_link($this->item->BuyableID, $this->item->ClassName,$this->parameters));
	}

	function DecrementLink(){
		return Convert::raw2att(ShoppingCart::decrement_item_link($this->item->BuyableID, $this->item->class,$this->parameters));
	}



	function forTemplate(){
		return $this->renderWith($this->template);
	}

}
