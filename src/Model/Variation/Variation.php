<?php

namespace SilverShop\Model\Variation;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Page\Product;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;

/**
 * Product Variation
 *
 * Provides a means for specifying many variations on a product.
 * Used in combination with ProductAttributes, such as color, size.
 * A variation will specify one particular combination, such as red, and large.
 *
 * @property string $InternalItemID
 * @property DBCurrency $Price
 * @property DBDecimal $Weight
 * @property DBDecimal $Height
 * @property DBDecimal $Width
 * @property DBDecimal $Depth
 * @property int $ProductID
 * @property int $ImageID
 * @method   Product Product()
 * @method   Image Image()
 * @method   AttributeValue[]|ManyManyList AttributeValues()
 */
class Variation extends DataObject implements Buyable
{
    private static $db = [
        'Sort' => 'Int',
        'InternalItemID' => 'Varchar(30)',
        'Price' => 'Currency(19,4)',

        //physical properties
        //TODO: Move to an extension
        'Weight' => 'Decimal(12,5)',
        'Height' => 'Decimal(12,5)',
        'Width' => 'Decimal(12,5)',
        'Depth' => 'Decimal(12,5)'
    ];

    private static $has_one = [
        'Product' => Product::class,
        'Image' => Image::class
    ];

    private static $owns = [
        'Image'
    ];

    private static $many_many = [
        'AttributeValues' => AttributeValue::class
    ];

    private static $casting = [
        'Title' => 'Text',
        'Price' => 'Currency'
    ];

    private static $versioning = [
        'Live'
    ];

    private static $extensions = [
        Versioned::class . '.versioned'
    ];

    private static $summary_fields = [
        'InternalItemID' => 'Product Code',
        //'Product.Title' => 'Product',
        'Title' => 'Variation',
        'Price' => 'Price'
    ];

    private static $searchable_fields = [
        'Product.Title',
        'InternalItemID'
    ];

    private static $indexes = [
        'InternalItemID' => true,
        'LastEdited' => true
    ];

    private static $singular_name = 'Variation';

    private static $plural_name = 'Variations';

    private static $default_sort = 'InternalItemID';

    private static $order_item = OrderItem::class;

    private static $table_name = 'SilverShop_Variation';

    /**
     * @config
     * @var bool
     */
    private static $title_has_label = true;

    /**
     * @config
     * @var string
     */
    private static $title_separator = ':';

    /**
     * @config
     * @var string
     */
    private static $title_glue = ', ';

    public function getCMSFields()
    {
        $fields = FieldList::create(
            TextField::create('InternalItemID', _t('SilverShop\Page\Product.Code', 'Product Code')),
            TextField::create('Price', _t('SilverShop\Page\Product.db_BasePrice', 'Price'))
        );
        //add attributes dropdowns
        $attributes = $this->Product()->VariationAttributeTypes();
        if ($attributes->exists()) {
            foreach ($attributes as $attribute) {
                if ($field = $attribute->getDropDownField()) {
                    if ($value = $this->AttributeValues()->find('TypeID', $attribute->ID)) {
                        $field->setValue($value->ID);
                    }
                    $fields->push($field);
                } else {
                    $fields->push(
                        LiteralField::create(
                            'novalues' . $attribute->Name,
                            '<p class="message warning">' .
                            _t(
                                __CLASS__ . '.NoAttributeValuesMessage',
                                '{attribute} has no values to choose from. You can create them in the "Products" &#62; "Product Attribute Type" section of the CMS.',
                                'Warning that will be shown if an attribute doesn\'t have any values',
                                ['attribute' => $attribute->Name]
                            ) .
                            '</p>'
                        )
                    );
                }
                //TODO: allow setting custom values here, rather than visiting the products section
            }
        } else {
            $fields->push(
                LiteralField::create(
                    'savefirst',
                    '<p class="message warning">' .
                    _t(
                        __CLASS__ . '.MustSaveFirstMessage',
                        'You can choose variation attributes after saving for the first time, if they exist.'
                    ) .
                    '</p>'
                )
            );
        }
        $fields->push(
            UploadField::create('Image', _t('SilverShop\Page\Product.Image', 'Product Image'))
        );

        //physical measurement units
        $fieldSubstitutes = [
            'LengthUnit' => Product::config()->length_unit
        ];

        //physical measurements
        $fields->push(
            TextField::create(
                'Weight',
                _t(
                    'SilverShop\Page\Product.WeightWithUnit',
                    'Weight ({WeightUnit})',
                    '',
                    [
                        'WeightUnit' => Product::config()->weight_unit
                    ]
                ),
                '',
                12
            )
        );

        $fields->push(
            TextField::create(
                'Height',
                _t('SilverShop\Page\Product.HeightWithUnit', 'Height ({LengthUnit})', '', $fieldSubstitutes),
                '',
                12
            )
        );

        $fields->push(
            TextField::create(
                'Width',
                _t('SilverShop\Page\Product.WidthWithUnit', 'Width ({LengthUnit})', '', $fieldSubstitutes),
                '',
                12
            )
        );

        $fields->push(
            TextField::create(
                'Depth',
                _t('SilverShop\Page\Product.DepthWithUnit', 'Depth ({LengthUnit})', '', $fieldSubstitutes),
                '',
                12
            )
        );

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * Save selected attributes - somewhat of a hack.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])) {
            $this->AttributeValues()->setByIDList(array_values($_POST['ProductAttributes']));
        }

        $img = $this->Image();

        if ($img && $img->exists()) {
            $img->doPublish();
        }
    }

    public function getTitle()
    {
        $values = $this->AttributeValues();
        if ($values->exists()) {
            $labelvalues = array();
            foreach ($values as $value) {
                if (self::config()->title_has_label) {
                    $labelvalues[] = $value->Type()->Label . self::config()->title_separator . $value->Value;
                } else {
                    $labelvalues[] = $value->Value;
                }
            }

            $title = implode(self::config()->title_glue, $labelvalues);
        }
        $this->extend('updateTitle', $title);

        return $title;
    }

    public function getCategoryIDs()
    {
        return $this->Product() ? $this->Product()->getCategoryIDs() : array();
    }

    public function getCategories()
    {
        return $this->Product() ? $this->Product()->getCategories() : ArrayList::create();
    }

    public function canPurchase($member = null, $quantity = 1)
    {
        $allowpurchase = false;
        if ($product = $this->Product()) {
            $allowpurchase =
                ($this->sellingPrice() > 0 || Product::config()->allow_zero_price) && $product->AllowPurchase;
        }

        $permissions = $this->extend('canPurchase', $member, $quantity);
        $permissions[] = $allowpurchase;
        return min($permissions);
    }

    /*
     * Returns if the product variation is already in the shopping cart.
     * @return boolean
     */
    public function IsInCart()
    {
        return $this->Item() && $this->Item()->Quantity > 0;
    }

    /*
     * Returns the order item which contains the product variation
     * @return  OrderItem
     */
    public function Item()
    {
        $filter = array();
        $this->extend('updateItemFilter', $filter);
        $item = ShoppingCart::singleton()->get($this, $filter);
        if (!$item) {
            //return dummy item so that we can still make use of Item
            $item = $this->createItem(0);
        }
        $this->extend('updateDummyItem', $item);
        return $item;
    }

    public function addLink()
    {
        return $this->Item()->addLink($this->ProductID, $this->ID);
    }

    /**
     * Returns a link to the parent product of this variation (variations don't have their own pages)
     *
     * @param $action string
     *
     * @return string
     */
    public function Link($action = null) {
        return ($this->ProductID) ? $this->Product()->Link($action) : false;
    }

    public function createItem($quantity = 1, $filter = array())
    {
        $orderitem = self::config()->order_item;
        $item = new $orderitem();
        $item->ProductID = $this->ProductID;
        $item->ProductVariationID = $this->ID;
        //$item->ProductVariationVersion = $this->Version;
        if ($filter) {
            //TODO: make this a bit safer, perhaps intersect with allowed fields
            $item->update($filter);
        }
        $item->Quantity = $quantity;
        return $item;
    }

    public function sellingPrice()
    {
        $price = $this->Price;
        $this->extend('updateSellingPrice', $price);

        //prevent negative values
        $price = $price < 0 ? 0 : $price;

        // NOTE: Ideally, this would be dependent on the locale but as of
        // now the Silverstripe Currency field type has 2 hardcoded all over
        // the place. In the mean time there is an issue where the displayed
        // unit price can not exactly equal the multiplied price on an order
        // (i.e. if the calculated price is 3.145 it will display as 3.15.
        // so if I put 10 of them in my cart I will expect the price to be
        // 31.50 not 31.45).
        return round($price, Order::config()->rounding_precision);
    }
}
