<?php

//Note: you may need to comment out ModelAdmin line 583 to disable checking file type for Excel exported files

class ProductBulkLoader extends CsvBulkLoader{
	
	static $parentpageid = null;
	
	public $columnMap = array(
	
		'Category' => '->setParent',
		'ProductGroup' => '->setParent',
		
		'Product ID' => 'InternalItemID',
		'ProductID' => 'InternalItemID',
		'SKU' => 'InternalItemID',
		
		'Long Description' => 'Content',
		'Short Description' => 'MetaDescription',
		
		'Short Title' => 'MenuTitle'
	);
	
	public $duplicateChecks = array(
		'InternalItemID' => 'InternalItemID', // use internalItemID for duplicate checks
		'Title' => 'Title'
	);
	
	public $relationCallbacks = array(
		'Image' => array(
			'relationname' => 'Image', // relation accessor name
			'callback' => 'imageByFilename'
		),
		'Photo' => array(
			'relationname' => 'Image', // relation accessor name
			'callback' => 'imageByFilename'
		)
	);
	
	
	protected function processAll($filepath, $preview = false) {
		$results = parent::processAll($filepath, $preview);
			
		//After results have been processed, publish all created & updated products
		$objects = new DataObjectSet();
		$objects->merge($results->Created());
		$objects->merge($results->Updated());
		foreach($objects as $object){
			
			$object->ClassName = "Product";
			
			if(!$object->ParentID){
				 //set parent page
				
				if(is_numeric(self::$parentpageid) &&  DataObject::get_by_id('ProductGroup',self::$parentpageid)) //cached option
					$object->ParentID = self::$parentpageid;
				elseif($parentpage = DataObject::get_one('ProductGroup',"Title = 'Products'",'Created DESC')){ //page called 'Products'
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}elseif($parentpage = DataObject::get_one('ProductGroup',"ParentID = 0",'Created DESC')){ //root page
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}elseif($parentpage = DataObject::get_one('ProductGroup',"",'Created DESC')){ //any product page
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}else
					$object->ParentID = self::$parentpageid = 0;
			}
			
			$object->extend('updateImport'); //could be used for setting other attributes, such as stock level
			
			$object->writeToStage('Stage'); 
			$object->publish('Stage', 'Live');
		}

		return $results;
	}
	
	// set image, based on filename
	function imageByFilename(&$obj, $val, $record){
		
		$filename = strtolower(Convert::raw2sql($val));
		if($filename && $image = DataObject::get_one('Image',"LOWER(Filename) LIKE '%$filename%'")){ //ignore case
			$image->ClassName = 'Product_Image'; //must be this type of image
			$image->write();
			return $image;
		}
		return null;
	}
	
	// find product group parent (ie Cateogry)	
	function setParent(&$obj, $val, $record){
		$title = strtolower(Convert::raw2sql($val));
		if($title && $parentpage = DataObject::get_one('ProductGroup',"LOWER(Title) = '$title'",'Created DESC')){ // find or create parent category, if provided
			$obj->ParentID = $parentpage->ID;
			$obj->write();
			$obj->writeToStage('Stage'); 
			$obj->publish('Stage', 'Live');
		}
	}
	
}

?>