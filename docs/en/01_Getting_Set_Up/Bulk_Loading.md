# Loading and modifying products via SpreadSheet

Products can be created and updated via spreadsheet. This is done in the Catalog section of the CMS. Make sure "Product" is selected on the left tab, then use the import form at the bottom to load your product spreadsheet.

## General Info

ProductID/SKU must be unique, otherwise duplicates will be overridden. If there is no ProductID, Title will be used instead.

If a column title matches a field on Product, it will be loaded in. eg if you specify an "AllowPurchase" column, the values will be loaded into each Product's AllowPurchase field.
Clicking the "Show Specification for Product" link in the Products section of the CMS will help you identify the possible fields to import to.

You are not required to fill out all fields for each product row.

You can find a test spreadsheet in tests/php/Admin/test_products.csv , which was exported from `tests/php/Admin/test_products.xls`.

## Assigning products to Categories

If you specify a 'Category' or 'ProductGroup' column, the loader will look for a ProductGroup with a title matching the specified category name. If you desire to use this system, it is recommended building your ProductGroup structure before loading the products.

## Additional Categories

If you have Additional Categories you would like to import you will need to Extend ProductBulkLoader to add "AdditionalCategories" to the ColumnMap.

The following will need the "AdditionalCategories" Column to be made up of page ID's. However there is an example below for using the Title.

    :::PHP
    class CSVImportingMap extends Extension {

        public function updateColumnMap(&$columnMap) {

            $columnMap['AdditionalCategories'] = '->processAdditionalCategories';
            return $columnMap;

        }

        public function processAdditionalCategories($obj, $val, $record) { 
            $categories = explode(",", $val); 
            foreach($categories as $category) { 
                if($category = ProductCategory::get()->byID($category)) {
                    $obj->ProductCategories()->add($category->ID); 
                } 
            } 
            return $obj; 
        }
    }
    
Use the following for "AdditionalCategories" Column made up of page Titles:

    ::PHP
    public function processAdditionalCategories($obj, $val, $record) { 
    $categories = explode(",", $val); 
        foreach($categories as $category) { 
            if($category = ProductCategory::get()->filter('Title', $category)->first()) {
                $obj->ProductCategories()->add($category->ID); 
            } 
        } 
        return $obj; 
    }

Then add the following to your .yml file:

    ::PHP
    ProductBulkLoader:
      extensions:
        - CSVImportingMap

## Product Images

Specifying a filename in a 'Image' or 'Photo' will link the product up to an image with the same filename that has been previously uploaded. This means you need to upload the product photos somewhere in the assets folder structure before doing the product bulk load.

The bulk loader does a search accross all folders for the right image. If you have filenames that are the same, you can specify the parent folder(s) to make the image reference unique. eg:

	Image
	banana.jpg
	Products/apple.jpg
	OtherProducts/apple.jpg

Note that case is ignored during this search.

## Product Variations

There are 6 column names dedicated to variations: 'Variation1' - 'Variation6'. Each cell should use the following format: VariationType: value,value,value

eg: Size:extra small,small,medium,large,extra large

Note: be careful how you name variation types, because all of the variation values will be assigned to common variation types. Eg: you could mistakingly end up with the following: `Size:extra small,small,medium,large,extra large,10,11,12,13,14`

### Trouble Shooting Tips

Make sure you don't have rouge spaces in your headings.

## Settings before you load

Set this if you want categories to be created if they don't exist.

	:::php
	ProductBulkLoader::set_create_new_product_groups(true);

You can force every product to be in a certain category, as long as you know it's id:

	:::php
	ProductBulkLoader::set_parent_page_id(..id here...);
