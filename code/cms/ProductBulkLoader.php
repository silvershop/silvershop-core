<?php

class ProductBulkLoader extends CsvBulkLoader{
	
	static $parentpageid = null;
	
	public $columnMap = array(
		'ProductID' => '->importClassName'
		
		//'Image' => '->linkToImage'//TODO: set image, based on filename (perhaps all product images could go in assets/products/)
	);
	
	public $duplicateChecks = array(
		'InternalItemID' => 'InternalItemID' // use internalItemID for duplicate checks
	);
	
	public $relationCallbacks = array();
	
	
	protected function processAll($filepath, $preview = false) {
		$results = parent::processAll($filepath, $preview);
		
		//After results have been processed, publish all created & updated products
		$objects = new DataObjectSet();
		$objects->merge($results->Created());
		$objects->merge($results->Updated());
		foreach($objects as $object){
			$object->writeToStage('Stage'); 
			$object->publish('Stage', 'Live');
		}
		return $results;
	}
	
	
	//Hack to make custom changes to new objects
	static function importClassName(&$obj, $val, $record){
		
		$obj->ClassName = "Product";
		
		 //TODO: find or create parent category, if provided
		
		 //set parent page
		if(self::$parentpageid instanceof ProductGroup) //cached option
			$obj->ParentID = self::$parentpageid;
		elseif($parentpage = DataObject::get_one('ProductGroup',"Title = 'Products'",'Created DESC')){ //page called 'Products'
			$obj->ParentID = self::$parentpageid = $parentpage->ID;
		}elseif($parentpage = DataObject::get_one('ProductGroup',"ParentID = 0",'Created DESC')){ //root page
			$obj->ParentID = self::$parentpageid = $parentpage->ID;
		}elseif($parentpage = DataObject::get_one('ProductGroup',"",'Created DESC')){ //any product page
			$obj->ParentID = self::$parentpageid = $parentpage->ID;
		}else
			$obj->ParentID = self::$parentpageid = 0;
		
	}
	
}


?>
