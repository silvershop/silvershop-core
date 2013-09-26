<?php

/**
 * SQLSorter is a reusable tool for handling SQL order by values.
 *  - handles: setup, storage, and validation
 *  
 * example:
 * <code>
 *  $sc = new SortControl("MySortControl");
 *  $sc->addSort("Alphabetical","A - Z", array("Title" => "ASC"));
 *  $sc->addSort("Category","Category",array("Category" => "ASC","Created" => "DESC"))
 *  $sc->addSort("LowPrice","Lowest Price",array("Price" => "ASC"));
 *	 $field = new DropDownField("Sort","Sort",$sc->getSortOptions(),$sc->getSortName()); 
 * </code>
 */
class SortControl{
	
	protected $sorts = array(), $name = "", $default = null;
	
	function __construct($name){
		$this->name = $name;
	}
	
	/**
	 * Add a sort value
	 * @param string $name - a unique name for this sorter, for saving to session.
	 * @param string $title - name of sorting value
	 * @param array $sorts
	 * @param string $default
	 */
	function addSort($name, $title, $fields, $default = false){
		if(!isset($this->sorts[$name])){
			$this->sorts[$name] = array(
				'title' => $title,
				'fields' => $fields	
			);
		}
		if(!$this->default || $default){
			$this->default = $name;
		}
	}
	
	/**
	 * Remove sort by name
	 * @param $name - the name of the sort to remove
	 */
	function removeSort($name){
		if(isset($this->sorts[$name])){
			unset($this->sorts[$name]);
			return true;
		}
		return false;
	}
	
	/**
	 * Clear all sorts
	 */
	function clearAll(){
		unset($this->sorts);
		$this->sorts = array();
		$this->default = null;
	}
	
	/**
	 * Create an array map of [sort name] -> [sort title]
	 */
	function getSortOptions(){
		$output = array();
		foreach($this->sorts as $name => $sort){
			$output[$name] = $sort['title'];
		}
		return $output;
	}
	
	/*
	 * Store the current sort
	 */
	function setSort($sort){
		if($this->validateSort($sort)){
			Session::set("SortControl_".$this->name,$sort);
			return true;
		}
		return false;
	}
	
	/*
	 * Get the current sort, or the default.
	 */
	function getSortName(){
		$current = Session::get("SortControl_".$this->name);
		return $current ? $current : $this->default;
	}
	
	/**
	 * Get the current sort fields as array
	 * @param string $combine
	 * @return boolean|multitype:string multitype:unknown
	 */
	function getSortArray($combine = false){
		if(!isset($this->sorts[$this->getSortName()]))
			return false;
		$sort = $this->sorts[$this->getSortName()];
		if(isset($sort['fields']) && is_array($sort['fields'])){
			$output = array();
			foreach($sort['fields'] as $field => $dir){
				if($combine){
					$output[] = $field." ".$dir;
				}else{
					$output[] = array(
						"sort" => $field,
						"dir" => $dir	
					);
				}
			}
			return $output;
		}
		return false;
	}
	
	/*
	 * Get sql statement for the current sort
	 */
	function getSortSQL(){
		if($ar = $this->getSortArray(true)){
			return implode(", ", $ar);
		}
		return null;
	}

	/**
	 * Checks if given sort exists;
	 */
	function validateSort($sort){
		return isset($this->sorts[$sort]);
	}
	
}