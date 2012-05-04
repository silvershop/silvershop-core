<?php

class OrderAttributeAJAX extends DataObjectDecorator{
	
	/**
	 * Return a string of class names, in order
	 * of heirarchy from OrderAttribute for the
	 * current attribute.
	 *
	 * e.g.: "product_orderitem orderitem
	 * orderattribute".
	 *
	 * Used by the templates and for ajax updating functionality.
	 *
	 * @return string
	 */
	function Classes() {
		$class = get_class($this);
		$classes = array();
		$classes[] = strtolower($class);
		while(get_parent_class($class) != 'DataObject' && $class = get_parent_class($class)) {
			$classes[] = strtolower($class);
		}
		return implode(' ', $classes);
	}
	
	function MainID() {
		return get_class($this).'_DB_'.$this->owner->ID;
	}
	
	function TableID() {
		return 'Table_' . $this->owner->MainID();
	}
	
	function CartID() {
		return 'Cart_' . $this->owner->MainID();
	}
	
	function ShowInCart() {
		return $this->owner->ShowInTable();
	}
	
	function TableTitleID() {
		return $this->owner->TableID() . '_Title';
	}
	
	function CartTitleID() {
		return $this->owner->CartID() . '_Title';
	}
	
	function TableTotalID() {
		return $this->owner->TableID() . '_Total';
	}
	
	function CartTotalID() {
		return $this->owner->CartID() . '_Total';
	}
	
}