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

	protected $orderItem = null;
	protected $parameters = null;
	protected $classes = array('ajaxQuantityField');
	protected $template = 'EcomQuantityField';

	function __construct($object, $parameters = null){
		Requirements::javascript("ecommerce/javascript/EcomQuantityField.js");
		Requirements::customScript("EcomQuantityField.set_hidePlusAndMins(".(EcomQuantityField::get_hide_plus_and_minus() ? 1 : 0).")");
		if(Object::has_extension($object->class,'Buyable')){
			$this->orderItem = ShoppingCart::get_order_item_by_buyableid($object->ID,$object->ClassName.Buyable::get_order_item_class_name_post_fix(),$parameters);
			 //provide a 0-quantity facade item if there is no such item in cart
			if(!$this->orderItem) {
				$this->orderItem = new Product_OrderItem($object->dataRecord,0);
			}
			//TODO: perhaps we should just store the product itself, and do away with the facade, as it might be unnecessary complication
		}
		elseif($object instanceof OrderItem && $object->BuyableID){
			$this->orderItem = $object;
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

	/**
	 *@return OrderItem
	 **/
	function Item(){
		return $this->OrderItem();
	}

	/**
	 *@return OrderItem
	 **/
	function OrderItem(){
		return $this->orderItem;
	}

	/**
	 *@return HTML
	 **/
	function Field() {
		$size = 3; //make these customisable
		$maxlength = 3;
		$attributes = array(
			'type' => 'text',
			'class' => implode(' ',$this->classes),
			'name' => $this->orderItem->MainID() . '_Quantity',
			'value' => ($this->orderItem->Quantity) ? $this->orderItem->Quantity : "",
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
		if($quantitylink = ShoppingCart::set_quantity_item_link($this->item->BuyableID, $this->orderItem->class,$this->parameters)){
			$attributes = array(
				'type' => 'hidden',
				'class' => 'ajaxQuantityField_qtylink',
				'name' => $this->orderItem->MainID() . '_Quantity_SetQuantityLink',
				'value' => $quantitylink
			);
			$formfield = new FormField('hack');
			return $formfield->createTag('input', $attributes);
		}
	}

	/**
	 *@return String(URLSegment)
	 **/
	function IncrementLink(){
		return Convert::raw2att(ShoppingCart::increment_item_link($this->item->BuyableID, $this->item->ClassName,$this->parameters));
	}

	/**
	 *@return String(URLSegment)
	 **/
	function DecrementLink(){
		return Convert::raw2att(ShoppingCart::decrement_item_link($this->item->BuyableID, $this->item->class,$this->parameters));
	}


	/**
	 *@return HTML
	 **/
	function forTemplate(){
		return $this->renderWith($this->template);
	}

}
