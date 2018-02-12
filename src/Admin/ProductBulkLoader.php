<?php

namespace SilverShop\Admin;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\ProductCategory;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Convert;
use SilverStripe\Dev\CsvBulkLoader;
use SilverStripe\ORM\ArrayList;

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
 * @package    shop
 * @subpackage cms
 */
class ProductBulkLoader extends CsvBulkLoader
{
    /**
     * You can force every product to be in a certain category, as long as you know its ID.
     *
     * @config
     * @var    null
     */
    private static $parent_page_id = null;

    /**
     * Set this if you want categories to be created if they don't exist.
     *
     * @config
     * @var    bool
     */
    protected static $create_new_product_groups = false;

    protected $foundParentId = null;


    // NB do NOT use functional indirection on any fields where they
    // will be used in $duplicateChecks as well - they simply don't work.
    public $columnMap = [
        'Price' => 'BasePrice',

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
        'Sub ID' => '->variationRow',
    ];

    public $duplicateChecks = [
        'InternalItemID' => 'InternalItemID',
        'SKU' => 'InternalItemID',
        'Product ID' => 'InternalItemID',
        'ProductID' => 'InternalItemID',
        'Title' => 'Title',
        'Page Title' => 'Title',
        'PageTitle' => 'Title',
    ];

    public $relationCallbacks = [
        'Image' => [
            'relationname' => 'Image', // relation accessor name
            'callback' => 'imageByFilename',
        ],
        'Photo' => [
            'relationname' => 'Image', // relation accessor name
            'callback' => 'imageByFilename',
        ],
    ];

    protected function processAll($filepath, $preview = false)
    {
        $this->extend('updateColumnMap', $this->columnMap);

        $results = parent::processAll($filepath, $preview);
        //After results have been processed, publish all created & updated products
        $objects = ArrayList::create();
        $objects->merge($results->Created());
        $objects->merge($results->Updated());
        $parentPageID = $this->config()->parent_page_id;
        foreach ($objects as $object) {
            if (!$object->ParentID) {
                //set parent page
                if (is_numeric($parentPageID) && ProductCategory::get()->byID($parentPageID)) { //cached option
                    $object->ParentID = $parentPageID;
                } elseif ($parentPage = ProductCategory::get()->filter('Title', 'Products')->sort('Created', 'DESC')->first()) { //page called 'Products'
                    $object->ParentID = $parentPageID = $parentPage->ID;
                } elseif ($parentpage = ProductCategory::get()->filter('ParentID', 0)->sort('Created', 'DESC')->first()) { //root page
                    $object->ParentID = $parentPageID = $parentpage->ID;
                } elseif ($parentpage = ProductCategory::get()->sort('Created', 'DESC')->first()) { //any product page
                    $object->ParentID = $parentPageID = $parentpage->ID;
                } else {
                    $object->ParentID = $parentPageID = 0;
                }
            }
            $this->foundParentId = $parentPageID;
            $object->extend('updateImport'); //could be used for setting other attributes, such as stock level
            $object->writeToStage('Stage');
            $object->publishSingle();
        }
        return $results;
    }

    public function processRecord($record, $columnMap, &$results, $preview = false)
    {
        if (!$record || !isset($record['Title']) || $record['Title'] == '') { //TODO: make required fields customisable
            return null;
        }
        return parent::processRecord($record, $columnMap, $results, $preview);
    }

    // set image, based on filename
    public function imageByFilename(&$obj, $val, $record)
    {
        $filename = trim(strtolower(Convert::raw2sql($val)));
        $filenamedashes = str_replace(' ', '-', $filename);
        if ($filename
            && $image = Image::get()->whereAny(
                [
                    "LOWER(\"FileFilename\") LIKE '%$filename%'",
                    "LOWER(\"FileFilename\") LIKE '%$filenamedashes%'"
                ]
            )->first()
        ) { //ignore case
            if ($image instanceof Image && $image->exists()) {
                return $image;
            }
        }
        return null;
    }

    // find product group parent (ie Cateogry)
    public function setParent(&$obj, $val, $record)
    {
        $title = strtolower(Convert::raw2sql($val));
        if ($title) {
            // find or create parent category, if provided
            if ($parentPage = ProductCategory::get()->where(['LOWER("Title") = ?' => $title])->sort('Created', 'DESC')->first()) {
                $obj->ParentID = $parentPage->ID;
                $obj->write();
                $obj->writeToStage('Stage');
                $obj->publishSingle();
                //TODO: otherwise assign it to the first product group found
            } elseif ($this->config()->create_new_product_groups) {
                //create parent product group
                $pg = ProductCategory::create();
                $pg->setTitle($title);
                $pg->ParentID = ($this->foundParentId) ? $this->foundParentId : 0;
                $pg->writeToStage('Stage');
                $pg->publishSingle();
                $obj->ParentID = $pg->ID;
                $obj->write();
                $obj->writeToStage('Stage');
                $obj->publishSingle();
            }
        }
    }

    /**
     * Adds paragraphs to content.
     */
    public function setContent(&$obj, $val, $record)
    {
        $val = trim($val);
        if ($val) {
            $paragraphs = explode("\n", $val);
            $obj->Content = '<p>' . implode('</p><p>', $paragraphs) . '</p>';
        }
    }

    public function processVariation(&$obj, $val, $record)
    {
        if (isset($record['->variationRow'])) {
            return;
        } //don't use this technique for variation rows
        $parts = explode(':', $val);
        if (count($parts) == 2) {
            $attributetype = trim($parts[0]);
            $attributevalues = explode(',', $parts[1]);
            //get rid of empty values
            foreach ($attributevalues as $key => $value) {
                if (!$value || trim($value) == '') {
                    unset($attributevalues[$key]);
                }
            }
            if (count($attributevalues) >= 1) {
                $attributetype = AttributeType::find_or_make($attributetype);
                foreach ($attributevalues as $key => $value) {
                    $val = trim($value);
                    if ($val != '' && $val != null) {
                        $attributevalues[$key] = $val; //remove outside spaces from values
                    }
                }
                $attributetype->addValues($attributevalues);
                $obj->VariationAttributeTypes()->add($attributetype);
                //only generate variations if none exist yet
                if (!$obj->Variations()->exists() || $obj->WeAreBuildingVariations) {
                    //either start new variations, or multiply existing ones by new variations
                    $obj->generateVariationsFromAttributes($attributetype, $attributevalues);
                    $obj->WeAreBuildingVariations = true;
                }
            }
        }
    }

    //work around until I can figure out how to allow calling processVariation multiple times
    public function processVariation1(&$obj, $val, $record)
    {
        $this->processVariation($obj, $val, $record);
    }

    public function processVariation2(&$obj, $val, $record)
    {
        $this->processVariation($obj, $val, $record);
    }

    public function processVariation3(&$obj, $val, $record)
    {
        $this->processVariation($obj, $val, $record);
    }

    public function processVariation4(&$obj, $val, $record)
    {
        $this->processVariation($obj, $val, $record);
    }

    public function processVariation5(&$obj, $val, $record)
    {
        $this->processVariation($obj, $val, $record);
    }

    public function processVariation6(&$obj, $val, $record)
    {
        $this->processVariation($obj, $val, $record);
    }

    public function variationRow(&$obj, $val, $record)
    {

        $obj->write(); //make sure product is in DB
        //TODO: or find existing variation
        $variation = Variation::get()->filter('InternalItemID', $val)->first();
        if (!$variation) {
            $variation = Variation::create();
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
        foreach ($varcols as $col) {
            if (isset($record[$col])) {
                $parts = explode(':', $record[$col]);
                if (count($parts) == 2) {
                    $attributetype = trim($parts[0]);
                    $attributevalues = explode(',', $parts[1]);
                    //get rid of empty values
                    foreach ($attributevalues as $key => $value) {
                        if (!$value || trim($value) == '') {
                            unset($attributevalues[$key]);
                        }
                    }
                    if (count($attributevalues) == 1) {
                        $attributetype = AttributeType::find_or_make($attributetype);
                        foreach ($attributevalues as $key => $value) {
                            $val = trim($value);
                            if ($val != '' && $val != null) {
                                $attributevalues[$key] = $val; //remove outside spaces from values
                            }
                        }
                        $attributetype->addValues($attributevalues); //create and add values to attribute type
                        $obj->VariationAttributeTypes()->add($attributetype); //add variation attribute type to product
                        //TODO: if existing variation, then remove current values
                        //record vairation attribute values (variation1, 2 etc)
                        foreach ($attributetype->convertArrayToValues($attributevalues) as $value) {
                            $variation->AttributeValues()->add($value);
                            break;
                        }
                    }
                }
            }
        }
        //copy db values into variation (InternalItemID, Price, Stock, etc) ...there will be unknowns from extensions.
        $dbfields = $variation->getSchema()->fieldSpecs(Variation::class);
        foreach ($record as $field => $value) {
            if (isset($dbfields[$field])) {
                $variation->$field = $value;
            }
        }
        $variation->write();
    }
}
