<?php
/**
 * ProductBulkLoader - allows loading products via CSV file.
 *
 * Images should be uploaded before import, where the Photo/Image field
 * corresponds to the filename of a file that was uploaded.
 *
 * Variations can be specified in a "Variation" column this format:
 * Type:value,value,value
 * eg: Color: red, green, blue , yellow
 * up to 6 other variation columns can be specified by adding a number to the end, eg Variation2,$Variation3
 *
 * @package shop
 * @subpackage cms
 */

class ProductBulkLoader extends CsvBulkLoader {

	public static $parentpageid = null;
	public static $createnewproductgroups = false;
	public static $hasStockImpl = false;

	// NB do NOT use functional indirection on any fields where they
		// will be used in $duplicateChecks as well - they simply don't work.
	public $columnMap = array(
		'Price' => 'BasePrice',
		'Cost Price' => 'CostPrice',

		'Category' => '->setParent',
		'ProductGroup' => '->setParent',
		'ProductCategory' => '->setParent',

		'Product ID' => 'InternalItemID',
		'ProductID' => 'InternalItemID',
		'SKU' => 'InternalItemID',

		'Description' => '->setContent',
		'Long Description' => '->setContent',
		'Short Description' => 'MetaDescription',

		'Short Title' => 'MenuTitle',

		'Title' => 'Title',
		'Page name' => 'Title',
		'Page Name' => 'Title',

		'Variation' => '->processVariation',
		'Variation1' => '->processVariation1',
		'Variation2' => '->processVariation2',
		'Variation3' => '->processVariation3',
		'Variation4' => '->processVariation4',
		'Variation5' => '->processVariation5',
		'Variation6' => '->processVariation6',

		'VariationID' => '->variationRow',
		'Variation ID' => '->variationRow',
		'SubID' => '->variationRow',
		'Sub ID' => '->variationRow'
	);

	public $duplicateChecks = array(
		'InternalItemID' => 'InternalItemID',
		'SKU' => 'InternalItemID',
		'Product ID' => 'InternalItemID',
		'ProductID' => 'InternalItemID',
		'Title' => 'Title',
		'Page Title' => 'Title',
		'PageTitle' => 'Title'
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
		$this->extend('updateColumnMap', $this->columnMap);
		// we have to check for the existence of this in case the stockcontrol module hasn't been loaded
		// and the CSV still contains a Stock column
		self::$hasStockImpl = Object::has_extension('Product', 'ProductStockDecorator');
		$results = parent::processAll($filepath, $preview);
		//After results have been processed, publish all created & updated products
		$objects = new ArrayList();
		$objects->merge($results->Created());
		$objects->merge($results->Updated());
		foreach($objects as $object){
			if(!$object->ParentID){
				//set parent page
				if(is_numeric(self::$parentpageid) && DataObject::get_by_id('ProductCategory', self::$parentpageid)){ //cached option
					$object->ParentID = self::$parentpageid;
				}elseif($parentpage = DataObject::get_one('ProductCategory', "\"Title\" = 'Products'", '"Created" DESC')){ //page called 'Products'
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}elseif($parentpage = DataObject::get_one('ProductCategory', "\"ParentID\" = 0", '"Created" DESC')){ //root page
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}elseif($parentpage = DataObject::get_one('ProductCategory', "", '"Created" DESC')){ //any product page
					$object->ParentID = self::$parentpageid = $parentpage->ID;
				}else{
					$object->ParentID = self::$parentpageid = 0;
				}
			}
			$object->extend('updateImport'); //could be used for setting other attributes, such as stock level
			$object->writeToStage('Stage');
			$object->publish('Stage', 'Live');
		}
		return $results;
	}

	public function processRecord($record, $columnMap, &$results, $preview = false) {
		if(!$record || !isset($record['Title']) || $record['Title'] == ''){ //TODO: make required fields customisable
			return null;
		}
		return parent::processRecord($record, $columnMap, $results, $preview);
	}

	// set image, based on filename
	public function imageByFilename(&$obj, $val, $record) {
		$filename = trim(strtolower(Convert::raw2sql($val)));
		$filenamedashes = str_replace(" ", "-", $filename);
		if($filename && $image = DataObject::get_one('Image', "LOWER(\"Filename\") LIKE '%$filename%' OR LOWER(\"Filename\") LIKE '%$filenamedashes%'")){ //ignore case
			if($image instanceof Image && $image->isInDB()){
				$image->write();
				return $image;
			}
		}
		return null;
	}

	// find product group parent (ie Cateogry)
	public function setParent(&$obj, $val, $record) {
		$title = strtolower(Convert::raw2sql($val));
		if($title){
			if($parentpage = DataObject::get_one('ProductCategory', "LOWER(\"Title\") = '$title'", '"Created" DESC')){ // find or create parent category, if provided
				$obj->ParentID = $parentpage->ID;
				$obj->write();
				$obj->writeToStage('Stage');
				$obj->publish('Stage', 'Live');
				//TODO: otherwise assign it to the first prodcut group found
			}elseif(self::$createnewproductgroups){
				//create parent product group
				$pg = new ProductCategory();
				$pg->setTitle($title);
				$pg->ParentID = (self::$parentpageid) ? $parentpageid :0;
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
	public function setContent(&$obj, $val, $record) {
		$val = trim($val);
		if($val){
			$paragraphs = explode("\n", $val);
			$obj->Content = "<p>".implode("</p><p>", $paragraphs)."</p>";
		}
	}

	public function processVariation(&$obj, $val, $record) {
		if(isset($record['->variationRow'])) return; //don't use this technique for variation rows
		$parts = explode(":", $val);
		if(count($parts) == 2){
			$attributetype = trim($parts[0]);
			$attributevalues = explode(",", $parts[1]);
			//get rid of empty values
			foreach($attributevalues as $key => $value){
				if(!$value || trim($value) == ""){
					unset($attributevalues[$key]);
				}
			}
			if(count($attributevalues) >= 1){
				$attributetype = ProductAttributeType::find_or_make($attributetype);
				foreach($attributevalues as $key => $value){
					$val = trim($value);
					if($val != "" && $val != null){
						$attributevalues[$key] = $val; //remove outside spaces from values
					}
				}
				$attributetype->addValues($attributevalues);
				$obj->VariationAttributeTypes()->add($attributetype);
				//only generate variations if none exist yet
				if(!$obj->Variations()->exists() || $obj->WeAreBuildingVariations){
					//either start new variations, or multiply existing ones by new variations
					$obj->generateVariationsFromAttributes($attributetype, $attributevalues);
					$obj->WeAreBuildingVariations = true;
				}
			}
		}
	}

	//work around until I can figure out how to allow calling processVariation multiple times
	public function processVariation1(&$obj, $val, $record) {
		$this->processVariation($obj, $val, $record);
	}
	public function processVariation2(&$obj, $val, $record) {
		$this->processVariation($obj, $val, $record);
	}
	public function processVariation3(&$obj, $val, $record) {
		$this->processVariation($obj, $val, $record);
	}
	public function processVariation4(&$obj, $val, $record) {
		$this->processVariation($obj, $val, $record);
	}
	public function processVariation5(&$obj, $val, $record) {
		$this->processVariation($obj, $val, $record);
	}
	public function processVariation6(&$obj, $val, $record) {
		$this->processVariation($obj, $val, $record);
	}

	public function variationRow(&$obj, $val, $record) {

		$obj->write(); //make sure product is in DB
		//TODO: or find existing variation
		$variation = ProductVariation::get()->filter("InternalItemID", '$val')->first();
		if(!$variation){
			$variation = new ProductVariation();
			$variation->InternalItemID = $val;
			$variation->ProductID = $obj->ID; //link to product
			$variation->write();
		}
		$varcols = array(
			'->processVariation',
			'->processVariation1',
			'->processVariation2',
			'->processVariation3',
			'->processVariation4',
			'->processVariation5',
			'->processVariation6',
		);
		foreach($varcols as $col){
			if(isset($record[$col])){
				$parts = explode(":", $record[$col]);
				if(count($parts) == 2){
					$attributetype = trim($parts[0]);
					$attributevalues = explode(",", $parts[1]);
					//get rid of empty values
					foreach($attributevalues as $key => $value){
						if(!$value || trim($value) == ""){
							unset($attributevalues[$key]);
						}
					}
					if(count($attributevalues) == 1){
						$attributetype = ProductAttributeType::find_or_make($attributetype);
						foreach($attributevalues as $key => $value){
							$val = trim($value);
							if($val != "" && $val != null){
								$attributevalues[$key] = $val; //remove outside spaces from values
							}
						}
						$attributetype->addValues($attributevalues); //create and add values to attribute type
						$obj->VariationAttributeTypes()->add($attributetype); //add variation attribute type to product
						//TODO: if existing variation, then remove current values
						//record vairation attribute values (variation1, 2 etc)
						foreach($attributetype->convertArrayToValues($attributevalues) as $value){
							$variation->AttributeValues()->add($value);
							break;
						}
					}

				}

			}
		}
		//copy db values into variation (InternalItemID, Price, Stock, etc) ...there will be unknowns from extensions.
		$dbfields = $variation->db();
		foreach($record as $field => $value){
			if(isset($dbfields[$field])){
				$variation->$field = $value;
			}
		}
		$variation->write();
	}

}
