<?php
/**
 * Remove all products and categories from the site.
 */
class DeleteProductsTask extends BuildTask{
	
	protected $title = "Delete Products";
	protected $description = "Removes all Products and Categories from the database.";
	
	function run($request){
		if($request->getVar("sqldelete") == "1"){
			$this->sqldelete();
		}else{
			$this->ormdelete();
		}
	}
	
	function ormdelete(){
		//TODO: convert to batch actions to handle large data sets
		if($allproducts = DataObject::get('Product')){
			foreach($allproducts as $product){
				$product->deleteFromStage('Live');
				$product->deleteFromStage('Stage');
				$product->destroy();
				//TODO: remove versions
			}
		}
		if($allcategories = DataObject::get('ProductCategory')){
			foreach($allcategories as $category){
				$category->deleteFromStage('Live');
				$category->deleteFromStage('Stage');
				$category->destroy();
				//TODO: remove versions
			}
		}
	}
	
	function sqldelete(){
		$basetables = array(
			'ProductCategory',
			'Product',
			'Product_Live','Product_versions','Product_ProductCategories','Product_OrderItem','Product_VariationAttributeTypes',
			'ProductVariation',
			'ProductVariation_AttributeValues','ProductVariation_OrderItem','ProductVariation_versions',
			'ProductAttributeType','ProductAttributeValue'
		);
		foreach($basetables as $table){
			if(!(ClassInfo::hasTable($table)))
				continue;
			foreach(ClassInfo::subclassesFor($table) as $key => $class){
				if(ClassInfo::hasTable($class)){
					DB::query("TRUNCATE TABLE \"$class\";");
					echo "<p>Deleting all $class</p>";
				}
			}
		}
		echo "<p>Deleting all Products, Categories, etc from SiteTree</p>";
		foreach(array('Product','ProductCategory') as $baseclass){
			foreach(ClassInfo::subclassesFor($baseclass) as $class){
				DB::query("DELETE FROM \"SiteTree\" WHERE ClassName = '$class';");
				DB::query("DELETE FROM \"SiteTree_Live\" WHERE ClassName = '$class';");
				DB::query("DELETE FROM \"SiteTree_versions\" WHERE ClassName = '$class';");
			}
		}
	}
	
}