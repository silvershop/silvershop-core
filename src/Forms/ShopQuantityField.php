<?php

namespace SilverShop\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Buyable;
use SilverShop\Model\OrderItem;
use SilverShop\ShopTools;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ViewableData;

class ShopQuantityField extends ViewableData
{
    protected OrderItem $item;

    protected $parameters;

    protected array $classes = ['ajaxQuantityField'];

    protected string $template = self::class;

    protected Buyable $buyable;

    /**
     * The max amount to enter
     *
     * @config
     */
    private static int $max = 0;

    public function __construct($object, $parameters = null)
    {
        parent::__construct();

        if ($object instanceof Buyable) {
            $this->item = ShoppingCart::singleton()->get($object, $parameters);
            //provide a 0-quantity facade item if there is no such item in cart
            if (!$this->item) {
                $this->item = $object->createItem();
            }
            $this->buyable = $object;
            //TODO: perhaps we should just store the product itself,
            //and do away with the facade, as it might be unnecessary complication
        } elseif ($object instanceof OrderItem) {
            $this->item = $object;
            $this->buyable = $object->Buyable();
        }
        if (!$this->item) {
            user_error('ShopQuantityField: no item or product passed to constructor.');
        }
        $this->parameters = $parameters;
        //TODO: include javascript for easy update
    }

    public function setClasses(array $newclasses, $overwrite = false): void
    {
        if ($overwrite) {
            $this->classes = array_merge($this->classes, $newclasses);
        } else {
            $this->classes = $newclasses;
        }
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function Item(): OrderItem
    {
        return $this->item;
    }

    public function Quantity(): int
    {
        return $this->item->Quantity;
    }

    public function Field()
    {
        $field = NumericField::create(
            $this->MainID() . '_Quantity',
            // this title currently doesn't show up in the front end, better assign a translation anyway.
            _t('SilverShop\Model\Order.Quantity', 'Quantity'),
            $this->item->Quantity
        )->setHTML5(true);

        if ($this->config()->max > 0) {
            $field->setAttribute("max", $this->config()->max);
        }

        return $field;
    }

    public function MainID(): string
    {
        return ShopTools::sanitiseClassName(get_class($this->item)) . '_DB_' . $this->item->ID;
    }

    public function IncrementLink(): string
    {
        return $this->item->addLink();
    }

    public function DecrementLink(): string
    {
        return $this->item->removeLink();
    }

    public function forTemplate(): DBHTMLText
    {
        return $this->renderWith($this->template);
    }

    /**
     * Used for storing the quantity update link for ajax use.
     */
    public function AJAXLinkHiddenField()
    {
        if ($quantitylink = $this->item->setquantityLink()) {
            return HiddenField::create(
                $this->MainID() . '_Quantity_SetQuantityLink'
            )->setValue($quantitylink)->addExtraClass('ajaxQuantityField_qtylink');
        }
    }
}
