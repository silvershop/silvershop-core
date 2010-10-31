<?php

class ProductBulkLoader extends CsvBulkLoader{
	
	static $parentpageid = null;
	
	public $columnMap = array(
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
			
			$object->ClassName = "Product";
			 //TODO: find or create parent category, if provided
			
			if(!$object->ParentID){
				 //set parent page
				if(self::$parentpageid instanceof ProductGroup) //cached option
					$object->ParentID = self::$parentpageid;
				elseif($parentpage = DataObject::get_one('ProductGroup',"\"Title\" = 'Products'",'"Created" DESC')){ //page called 'Products'
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}elseif($parentpage = DataObject::get_one('ProductGroup',"\"ParentID\" = 0",'"Created" DESC')){ //root page
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}elseif($parentpage = DataObject::get_one('ProductGroup',"",'"Created" DESC')){ //any product page
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}else
					$object->ParentID = self::$parentpageid = 0;
			}
			
			$object->writeToStage('Stage'); 
			$object->publish('Stage', 'Live');
		}
		return $results;
	}
	

	
}


?>
