<?php

declare(strict_types=1);

namespace SilverShop\Page;

use SilverStripe\Forms\FormField;
use Exception;
use Page;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Currency\CurrencyService;
use SilverShop\Extension\ProductVariationsExtension;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Model\Product\OrderItem;
use SilverShop\Model\Product\ProductCurrencyPrice;
use SilverShop\Model\Variation\Variation;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * This is a standard Product page-type with fields like
 * Price, Weight, Model and basic management of
 * groups.
 *
 * It also has an associated Product_OrderItem class,
 * an extension of OrderItem, which is the mechanism
 * that links this page type class to the rest of the
 * eCommerce platform. This means you can add an instance
 * of this page type to the shopping cart.
 *
 * @mixin ProductVariationsExtension
 *
 * @property ?string $InternalItemID
 * @property ?string $Model
 * @property float $BasePrice
 * @property ?float $TaxRate
 * @property float $Weight
 * @property float $Height
 * @property float $Width
 * @property float $Depth
 * @property bool $Featured
 * @property bool $AllowPurchase
 * @property float $Popularity
 * @property int $ImageID
 *
 * @method ManyManyList<ProductCategory> ProductCategories()
 * @method Image Image()
 * @method HasManyList<ProductCurrencyPrice> Prices()
 */
class Product extends Page implements Buyable
{
    private static array $db = [
        'InternalItemID' => 'Varchar(30)', //ie SKU, ProductID etc (internal / existing recognition of product)
        'Model' => 'Varchar(30)',
        'BasePrice' => 'Currency(19,4)', // Base retail price the item is marked at.
        'TaxRate' => 'Double',
        //physical properties
        // TODO: Move these to an extension (used in Variations as well)
        'Weight' => 'Decimal(12,5)',
        'Height' => 'Decimal(12,5)',
        'Width' => 'Decimal(12,5)',
        'Depth' => 'Decimal(12,5)',
        'Featured' => 'Boolean',
        'AllowPurchase' => 'Boolean',
        'Popularity' => 'Float' //storage for CalculateProductPopularity task
    ];

    private static array $has_one = [
        'Image' => Image::class,
    ];

    private static array $has_many = [
        'Prices' => ProductCurrencyPrice::class,
    ];

    /**
     * Variations are defined on {@see ProductVariationsExtension}; duplicating a product should copy them.
     * Cascade deletes for variations are handled in that extension for correct Versioned behaviour.
     */
    private static array $cascade_duplicates = [
        'Variations',
        'Prices',
    ];

    private static array $cascade_deletes = [
        'Prices',
    ];

    private static array $owns = [
        'Image'
    ];

    private static array $many_many = [
        'ProductCategories' => ProductCategory::class,
    ];

    private static array $defaults = [
        'AllowPurchase' => true,
        'ShowInMenus' => false,
    ];

    private static array $casting = [
        'Price' => 'Currency',
    ];

    private static array $summary_fields = [
        'InternalItemID',
        'Title',
        'BasePrice.NiceOrEmpty',
        'IsPurchaseable.Nice',
    ];

    private static array $searchable_fields = [
        'InternalItemID',
        'Title',
        'Featured',
    ];

    private static string $table_name = 'SilverShop_Product';

    private static string $singular_name = 'Product';

    private static string $plural_name = 'Products';

    private static string $cms_icon_class = 'font-icon-p-package';

    private static string $default_parent = ProductCategory::class;

    private static string $default_sort = '"Title" ASC';

    private static bool $global_allow_purchase = true;

    private static bool $allow_zero_price = false;

    private static string $order_item = OrderItem::class;

    // Physical Measurement
    private static string $weight_unit = 'kg';

    private static string $length_unit = 'cm';

    private static array $indexes = [
        'Featured' => true,
        'AllowPurchase' => true,
        'InternalItemID' => true,
    ];

    /**
     * Add product fields to CMS
     *
     * @return FieldList updated field list
     */
    public function getCMSFields(): FieldList
    {
        $self = $this;

        $this->beforeUpdateCMSFields(
            function (FieldList $fieldList) use ($self): void {
                $fieldList->fieldByName('Root.Main.Title')
                    ->setTitle(_t(__CLASS__ . '.PageTitle', 'Product Title'));

                $fieldList->addFieldsToTab('Root.Main', [
                    TextField::create('InternalItemID', _t(__CLASS__ . '.InternalItemID', 'Product Code/SKU'), '', 30),
                    DropdownField::create('ParentID', _t(__CLASS__ . '.Category', 'Category'), $self->getCategoryOptions())
                        ->setDescription(_t(__CLASS__ . '.CategoryDescription', 'This is the parent page or default category.')),
                    ListboxField::create(
                        'ProductCategories',
                        _t(__CLASS__ . '.AdditionalCategories', 'Additional Categories'),
                        $self->getCategoryOptionsNoParent()
                    ),
                    TextField::create('Model', _t(__CLASS__ . '.Model', 'Model'), '', 30),
                    CheckboxField::create('Featured', _t(__CLASS__ . '.Featured', 'Featured Product')),
                    CheckboxField::create('AllowPurchase', _t(__CLASS__ . '.AllowPurchase', 'Allow product to be purchased'), 1),
                ], 'Content');

                $fieldList->addFieldsToTab(
                    'Root.Pricing',
                    [
                        TextField::create('BasePrice', $this->fieldLabel('BasePrice'))
                        ->setDescription(_t(__CLASS__ . '.PriceDesc', 'Base price to sell this product at.'))
                        ->setMaxLength(12),
                        TextField::create('TaxRate', $this->fieldLabel('TaxRate'))
                        ->setDescription(_t(__CLASS__ . '.TaxRateDesc', 'Optional tax rate for this product only (for example 0.15 for 15%). Leave blank to use the default order tax rate.'))
                        ->setMaxLength(12),
                    ]
                );

                // Add per-currency prices grid if the product has been saved
                if ($self->isInDB()) {
                    $fieldList->addFieldToTab(
                        'Root.Pricing',
                        GridField::create(
                            'Prices',
                            _t(__CLASS__ . '.CurrencyPrices', 'Prices per Currency'),
                            $self->Prices(),
                            GridFieldConfig_RecordEditor::create()
                        )
                    );
                }

                $fieldSubstitutes = [
                    'LengthUnit' => $self::config()->length_unit
                ];

                $fieldList->addFieldsToTab(
                    'Root.Shipping',
                    [
                    TextField::create(
                        'Weight',
                        _t(
                            __CLASS__ . '.WeightWithUnit',
                            'Weight ({WeightUnit})',
                            '',
                            [
                            'WeightUnit' => self::config()->weight_unit
                            ]
                        ),
                        '',
                        12
                    ),
                    TextField::create(
                        'Height',
                        _t(__CLASS__ . '.HeightWithUnit', 'Height ({LengthUnit})', '', $fieldSubstitutes),
                        '',
                        12
                    ),
                    TextField::create(
                        'Width',
                        _t(__CLASS__ . '.WidthWithUnit', 'Width ({LengthUnit})', '', $fieldSubstitutes),
                        '',
                        12
                    ),
                    TextField::create(
                        'Depth',
                        _t(__CLASS__ . '.DepthWithUnit', 'Depth ({LengthUnit})', '', $fieldSubstitutes),
                        '',
                        12
                    ),
                    ]
                );

                if (!$fieldList->dataFieldByName('Image') instanceof FormField) {
                    $fieldList->addFieldToTab(
                        'Root.Images',
                        UploadField::create('Image', _t(__CLASS__ . '.Image', 'Product Image'))
                    );
                }
            }
        );

        return parent::getCMSFields();
    }

    /**
     * Add missing translations to the fieldLabels
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);

        $labels['Title'] = _t(__CLASS__ . '.PageTitle', 'Product Title');
        $labels['IsPurchaseable'] = $labels['IsPurchaseable.Nice'] = _t(__CLASS__ . '.IsPurchaseable', 'Is Purchaseable');
        $labels['BasePrice.NiceOrEmpty'] = _t(__CLASS__ . '.db_BasePrice', 'Price');
        $labels['TaxRate'] = _t(__CLASS__ . '.db_TaxRate', 'Tax Rate');

        return $labels;
    }

    /**
     * Helper function for generating list of categories to select from.
     *
     * @return array categories
     */
    private function getCategoryOptions(): array
    {
        $categories = ProductCategory::get()->map('ID', 'NestedTitle')->toArray();
        $categories = [
            0 => _t('SilverStripe\CMS\Model\SiteTree.PARENTTYPE_ROOT', 'Top-level page'),
        ] + $categories;
        if ($this->ParentID && !($this->Parent() instanceof ProductCategory)) {
            return [
                $this->ParentID => $this->Parent()->Title . ' (' . $this->Parent()->i18n_singular_name() . ')',
            ] + $categories;
        }

        return $categories;
    }

    /**
     * Helper function for generating a list of additional categories excluding the main parent.
     *
     * @return array categories
     */
    private function getCategoryOptionsNoParent(): array
    {
        $ancestors = $this->getAncestors()->column('ID');
        $categories = ProductCategory::get();
        if (!empty($ancestors)) {
            $categories = $categories->exclude(['ID' => $ancestors]);
        }

        return $categories->map('ID', 'NestedTitle')->toArray();
    }

    /**
     * Get ids of all categories that this product appears in.
     *
     * @return array ids list
     */
    public function getCategoryIDs(): array
    {
        $ids = [];
        //ancestors
        foreach ($this->getAncestors() as $ancestor) {
            $ids[$ancestor->ID] = $ancestor->ID;
        }

        //additional categories
        $ids += $this->ProductCategories()->getIDList();

        return $ids;
    }

    /**
     * Get all categories that this product appears in.
     *
     * @return DataList category data list
     */
    public function getCategories(): DataList
    {
        return ProductCategory::get()->byIDs($this->getCategoryIDs());
    }

    /**
     * Conditions for whether a product can be purchased:
     *  - global allow purchase is enabled
     *  - product AllowPurchase field is true
     *  - if variations, then one of them needs to be purchasable
     *  - if not variations, selling price must be above 0
     *
     * Other conditions may be added by decorating with the canPurchase function
     */
    public function canPurchase(?Member $member = null, int $quantity = 1): bool
    {
        $global = self::config()->global_allow_purchase;

        if (!$global || !$this->AllowPurchase) {
            return false;
        }

        $allowPurchase = false;
        $extension = self::has_extension(ProductVariationsExtension::class);

        if ($extension && Variation::get()->filter(['ProductID' => $this->ID])->first()) {
            foreach ($this->Variations() as $hasManyList) {
                if ($hasManyList->canPurchase($member, $quantity)) {
                    $allowPurchase = true;
                    break;
                }
            }
        } else {
            $allowPurchase = ($this->sellingPrice() > 0 || self::config()->allow_zero_price);
        }

        // Standard mechanism for accepting permission changes from decorators
        $permissions = $this->extend('canPurchase', $member, $quantity);
        $permissions[] = $allowPurchase;

        return min($permissions);
    }

    /**
     * Returns the purchaseable flag as `DBBoolean`. Useful for templates or summaries.
     */
    public function IsPurchaseable(): DBBoolean
    {
        return DBBoolean::create_field(DBBoolean::class, $this->canPurchase());
    }

    /**
     * Returns if the product is already in the shopping cart.
     */
    public function IsInCart(): bool
    {
        $orderItem = $this->Item();
        return $orderItem instanceof \SilverShop\Model\OrderItem && $orderItem->exists() && $orderItem->Quantity > 0;
    }

    /**
     * Returns the order item which contains the product
     */
    public function Item(): OrderItem|\SilverShop\Model\OrderItem
    {
        $filter = [];
        $this->extend('updateItemFilter', $filter);
        $item = ShoppingCart::singleton()->get($this, $filter);
        if (!$item) {
            //return dummy item so that we can still make use of Item
            $item = $this->createItem();
        }

        $this->extend('updateDummyItem', $item);
        return $item;
    }

    /**
     * @see Buyable::createItem()
     */
    public function createItem($quantity = 1, $filter = null): OrderItem
    {
        $orderItem = self::config()->order_item;

        if (!$orderItem) {
            $orderItem = OrderItem::class;
        }

        $item = Injector::inst()->create($orderItem);
        $item->ProductID = $this->ID;

        if ($filter) {
            $item->update($filter);
        }

        $item->Quantity = $quantity;

        return $item;
    }

    /**
     * The raw retail price the visitor will get when they
     * add to cart. Can include discounts or markups on the base price.
     *
     * If the shop has multi-currency enabled, this will return the price in the
     * currently active currency. If a specific price has been defined for that
     * currency it will be used directly; otherwise the base price will be
     * converted using the active {@link CurrencyService}.
     */
    public function sellingPrice(): float
    {
        $price = $this->BasePrice;

        // Check for a currency-specific price override
        $currencyService = Injector::inst()->get(CurrencyService::class);
        $activeCurrency = $currencyService->getActiveCurrency();
        $baseCurrency = ShopConfigExtension::get_site_currency();

        if ($activeCurrency !== $baseCurrency) {
            // Check for a directly-defined price for this currency
            $currencyPrice = $this->Prices()->filter('Currency', $activeCurrency)->first();
            if ($currencyPrice && $currencyPrice->exists()) {
                $price = $currencyPrice->Price;
            } else {
                // Fall back to currency conversion
                $price = $currencyService->convert((float)$price, $baseCurrency, $activeCurrency);
            }
        }

        $this->extend('updateSellingPrice', $price);

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

    /**
     * This value is cased to Currency in temlates.
     */
    public function getPrice(): float
    {
        return $this->sellingPrice();
    }

    public function setPrice($price): void
    {
        $price = $price < 0 ? 0 : $price;
        $this->setField('BasePrice', $price);
    }

    /**
     * Allow orphaned products to be viewed.
     */
    public function isOrphaned(): bool
    {
        return false;
    }

    /**
     * If the product does not have an image, and a default image
     * is defined in SiteConfig, return that instead.
     *
     * @return Image
     * @throws Exception
     */
    public function Image(): ?Image
    {
        $image = $this->getComponent('Image');
        $this->extend('updateImage', $image);

        if ($image && $image->exists()) {
            return $image;
        }

        $image = SiteConfig::current_site_config()->DefaultProductImage();
        if ($image && $image->exists()) {
            return $image;
        }

        return null;
    }

    /**
     * Link to add this product to cart.
     */
    public function addLink(): string|bool
    {
        return CartPageController::add_item_link($this);
    }

    /**
     * Link to remove one of this product from cart.
     *
     * @return string|false link
     */
    public function removeLink(): string|bool
    {
        return CartPageController::remove_item_link($this);
    }

    /**
     * Link to remove all of this product from cart.
     *
     * @return string|false link
     */
    public function removeAllLink(): string|bool
    {
        return CartPageController::remove_all_item_link($this);
    }

    /**
     * POST target for bulk-adding variations from the variations table form.
     */
    public function getVariationsBulkAddLink(): string
    {
        return CartPage::find_link(false, 'addvariations');
    }

    public function getVariationsBulkSecurityTokenName(): string
    {
        return SecurityToken::inst()->getName();
    }

    public function getVariationsBulkSecurityTokenValue(): string
    {
        return SecurityToken::inst()->getValue();
    }

    public function getVariationsBulkSecurityTokenRequired(): bool
    {
        return SecurityToken::is_enabled();
    }

    /**
     * True when at least one purchasable variation is not yet in the cart (shows bulk submit).
     */
    public function getShowVariationsBulkAddButton(): bool
    {
        $cart = ShoppingCart::singleton();

        foreach ($this->Variations() as $variation) {
            if (!$variation->canPurchase()) {
                continue;
            }

            if ($cart->get($variation) === null) {
                return true;
            }
        }

        return false;
    }
}
