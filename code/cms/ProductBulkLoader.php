<?php
/**
 * @Description: ProductBulkLoader - allows loading products via CSV file.
 * Images should be uploaded before import,
 * where the Photo/Image field corresponds to the filename of a file that was uploaded.
 *
 * @authors: Silverstripe, Jeremy, Tony, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 */

class ProductBulkLoader extends CsvBulkLoader{

	protected static $product_class_name = "Product";
		static function set_product_class_name($s) {self::$product_class_name = $s;}
		static function get_product_class_name() {return self::$product_class_name;}

	protected static $product_group_class_name = "ProductGroup";
		static function set_product_group_class_name($s) {self::$product_group_class_name = $s;}
		static function get_product_group_class_name() {return self::$product_group_class_name;}

	protected static $parent_page_id = null;
		static function set_parent_page_id(int $i) {self::$parent_page_id = intval($i);}
		static function get_parent_page_id() {return self::$parent_page_id;}

	protected static $create_new_product_groups = false;
		static function set_create_new_product_groups($b = true) {self::$create_new_product_groups = $b;}
		static function get_create_new_product_groups() {return self::$create_new_product_groups;}

	public $columnMap = array(
		'Category' => '->setParent',
		'ProductGroup' => '->setParent',

		'Product ID' => 'InternalItemID',
		'ProductID' => 'InternalItemID',
		'SKU' => 'InternalItemID',

		'Description' => '->setContent',
		'Long Description' => '->setContent',
		'Short Description' => 'MetaDescription',

		'Short Title' => 'MenuTitle',

		'Title' => 'Title',
		
		//'Delete' //TODO: allow products to be deleted/disabled via CSV
	);

	/* 	NB there is a bug in CsvBulkLoader where it fails to apply Convert::raw2sql to the field value prior to a duplicate check.
	 	This results in a failed database call on any fields here that conatin quotes and causes whole load to fail.
	 	Fix is to change CsvBulkLoader findExistingObject function
	 	FROM
	 		$SQL_fieldValue = $record[$fieldName];
	 	TO
	 		$SQL_fieldValue = Convert::raw2sql($record[$fieldName]);
	 	until patch gets applied by SS team
	*/

	public $duplicateChecks = array(
		'InternalItemID' => 'InternalItemID',
		//'Product ID' => 'InternalItemID', // see issue 143
		//'ProductID' => 'InternalItemID',
		//'SKU' => 'InternalItemID',
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

	static function import_content(&$obj, $val, $record ){
		$obj->Content = Convert::raw2sql($val);
	}
	static function import_meta_description(&$obj, $val, $record ){
		$obj->MetaDescription = Convert::raw2sql($val);
	}
	static function import_menu_title(&$obj, $val, $record ){
		$obj->MenuTitle = Convert::raw2sql($val);
	}
	static function import_title(&$obj, $val, $record ){
		$obj->Title = Convert::raw2sql($val);
	}

	static function importInternalItemID(&$obj, $val, $record ){
		$obj->InternalItemID = Convert::raw2sql($val);
	}

	protected function processAll($filepath, $preview = false) {
		$this->extend('updateColumnMap',$this->columnMap);
		// we have to check for the existence of this in case the stockcontrol module hasn't been loaded
		// and the CSV still contains a Stock column

		$results = parent::processAll($filepath, $preview);

		//After results have been processed, publish all created & updated products
		$objects = new DataObjectSet();
		$objects->merge($results->Created());
		$objects->merge($results->Updated());
		foreach($objects as $object){
			if(!$object->ParentID){
				 //set parent page
				 //cached option
				if(is_numeric(self::$parent_page_id) &&  DataObject::get_by_id(self::get_product_group_class_name(),self::$parent_page_id)) {
					$object->ParentID = self::$parent_page_id;
				}
				//page called 'Products'
				elseif($parentpage = DataObject::get_one(self::get_product_group_class_name(),"\"Title\" = 'Products'",'"Created" DESC')){
					$object->ParentID = self::$parent_page_id = $parentpage->ID;
				}
				//root page
				elseif($parentpage = DataObject::get_one(self::get_product_group_class_name(),"\"ParentID\" = 0",'"Created" DESC')){
					$object->ParentID = self::$parent_page_id = $parentpage->ID;
				}
				//any product page
				elseif($parentpage = DataObject::get_one(self::get_product_group_class_name(),"",'"Created" DESC')){
					$object->ParentID = self::$parent_page_id = $parentpage->ID;
				}
				else {
					$object->ParentID = self::$parent_page_id = 0;
				}
			}

			$object->extend('updateImport'); //could be used for setting other attributes, such as stock level

			$object->writeToStage('Stage');
			$object->publish('Stage', 'Live');
		}

		return $results;
	}

	function processRecord($record, $columnMap, &$results, $preview = false){
		//see issue 144
		if(!$record || !isset($record['Title']) || $record['Title'] == ''){
			return null;
		}
		return parent::processRecord($record, $columnMap, $results, $preview);
	}

	/**
	 *set image, based on filename
	 *@return Image Object
	 **/
	function imageByFilename(&$obj, $val, $record){
		$filename = strtolower(Convert::raw2sql($val));
		if($filename && $image = DataObject::get_one('Image',"LOWER(\"Filename\") LIKE '%$filename%'")){ //ignore case
			if($image->ID){
				$image->ClassName = self::get_product_class_name().'_Image'; //must be this type of image
				$image->write();
				return $image;
			}
		}
		return null;
	}

	// find product group parent (ie Cateogry)
	function setParent(&$obj, $val, $record){
		$title = strtolower(Convert::raw2sql($val));
		if($title){
			if($parentpage = DataObject::get_one(self::get_product_group_class_name(),"LOWER(\"Title\") = '$title'",'"Created" DESC')){ // find or create parent category, if provided
				$obj->ParentID = $parentpage->ID;
				$obj->write();
				$obj->writeToStage('Stage');
				$obj->publish('Stage', 'Live');
			}
			elseif(self::$create_new_product_groups){
				$className = self::get_product_group_class_name();
				//create parent product group
				$pg = new $className();
				$pg->setTitle($title);
				$pg->ParentID = (self::$parent_page_id) ? self::$parent_page_id : 0;
				$pg->writeToStage('Stage');
				$pg->publish('Stage', 'Live');
				$obj->ParentID = $pg->ID;
				$obj->write();
				$obj->writeToStage('Stage');
				$obj->publish('Stage', 'Live');
			}
		}
	}

	/**
	 * Adds paragraphs to content.
	 */
	function setContent(&$obj, $val, $record){
		$val = trim($val);
		if($val){
			$paragraphs = explode("\n",$val);
			$obj->Content = "<p>".implode("</p><p>",$paragraphs)."</p>";
		}
	}

}

