<?php
/**
 * Data class that keeps a log of a single
 * status of an order.
 * 
 * @package ecommerce
 */
class OrderStatusLog extends DataObject {
	
	public static $db = array(
		'Status' => 'Varchar(255)',
		'Note' => 'Text',
		'SentToCustomer' => 'Boolean'
	);
	
	public static $has_one = array(
		'Author' => 'Member',
		'Order' => 'Order'
	);
	
	public static $has_many = array();
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public static $defaults = array();
	
	public static $casting = array();
	
	function onBeforeSave() {
		if(!$this->ID) {
			$this->AuthorID = Member::currentUser()->ID;
		}
		
		parent::onBeforeSave();
	}
}
?>