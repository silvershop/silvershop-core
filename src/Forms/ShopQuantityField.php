<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Model\ModelData;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Buyable;
use SilverShop\Model\OrderItem;
use SilverShop\ShopTools;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class ShopQuantityField extends ModelData
{
    protected OrderItem $item;

    protected $parameters;

    protected array $classes = ['silvershop-ajax-quantity-field'];

    protected string $template = self::class;

    protected Buyable $buyable;

    /**
     * The max amount to enter
     */
    private static int $max = 0;

    public function __construct($object, $parameters = null)
    {
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
        $this->classes = $overwrite ? array_merge($this->classes, $newclasses) : $newclasses;
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
        $numericField = NumericField::create(
            $this->MainID() . '_Quantity',
            // this title currently doesn't show up in the front end, better assign a translation anyway.
            _t('SilverShop\Model\Order.Quantity', 'Quantity'),
            $this->item->Quantity
        )->setHTML5(true);

        foreach ($this->classes as $className) {
            $numericField->addExtraClass($className);
        }

        if ($this->config()->max > 0) {
            $numericField->setAttribute("max", $this->config()->max);
        }

        return $numericField;
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

    public function forTemplate(): string
    {
        return $this->renderWith($this->template);
    }

    /**
     * Used for storing the quantity update link for ajax use.
     */
    public function AJAXLinkHiddenField()
    {
        if (($quantitylink = $this->item->setquantityLink()) !== '' && ($quantitylink = $this->item->setquantityLink()) !== '0') {
            return HiddenField::create(
                $this->MainID() . '_Quantity_SetQuantityLink'
            )->setValue($quantitylink)->addExtraClass('silvershop-ajax-quantity-field__qty-link');
        }
    }
}
