<?php

namespace SilverShop\Forms;

use Closure;
use SilverShop\Model\Order;
use SilverShop\Model\Variation\Variation;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\SSViewer;

/**
 * Field for editing cart/items within a form
 *
 * @package shop
 */
class CartEditField extends FormField
{
    protected Order $cart;
    protected SS_List $items;
    protected $template = 'Cart';
    protected $editableItemsCallback;

    public function __construct($name, $title, $cart)
    {
        parent::__construct($name, $title);
        $this->cart = $cart;
        $this->items = $cart->Items();
    }

    /**
     * Set tempalte for rendering editable cart.
     *
     * @param string $template
     */
    public function setTemplate($template): static
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Allow overriding the given items list.
     * This helps with formatting, grouping, ordering etc.
     */
    public function setItemsList(SS_List $ssList): static
    {
        $this->items = $ssList;
        return $this;
    }

    /**
     * Get the items list being used to produce the cart.
     */
    public function getItemsList(): SS_List
    {
        return $this->items;
    }

    /**
     * Provides a way to modify the editableItems list
     * before it is rendered.
     */
    public function setEditableItemsCallback(Closure $callback): void
    {
        $this->editableItemsCallback = $callback;
    }

    /**
     * Render the cart with editable item fields.
     *
     * Note: SilverStripe\Forms\FormField\Field returns DBHTMLText
     * @param array $properties
     * @return string
     */
    public function Field($properties = [])
    {
        $editables = $this->editableItems();
        $customcartdata = [
            'Items' => $editables,
        ];
        // NOTE: this was originally incorrect - passing just $editables and $customcartdata
        // which broke modules like Display_Logic.
        $this->extend('onBeforeRender', $this, $editables, $customcartdata);

        return SSViewer::execute_template(
            $this->template,
            $this->cart->customise($customcartdata),
            ['Editable' => true]
        );
    }

    /**
     * Add quantity, variation and remove fields to the
     * item set.
     */
    protected function editableItems()
    {
        $editables = ArrayList::create();
        foreach ($this->items as $item) {
            $buyable = $item->Buyable();
            if (!$buyable) {
                continue;
            }
            // If the buyable is a variation, use the belonging product instead for variation-form generation
            if ($buyable instanceof Variation) {
                $buyable = $buyable->Product();
            }
            $name = $this->name . "[$item->ID]";
            $quantity = TextField::create(
                $name . '[Quantity]',
                'Quantity',
                $item->Quantity
            )
                ->addExtraClass('numeric')
                ->setAttribute('type', 'number')
                ->setAttribute('min', '0');

            $variationfield = false;
            if ($buyable->hasMany('Variations')) {
                $variations = $buyable->Variations();
                if ($variations->exists()) {
                    $variationfield = DropdownField::create(
                        $name . '[ProductVariationID]',
                        _t('SilverShop\Model\Variation\Variation.SINGULARNAME', 'Variation'),
                        $variations->map('ID', 'Title'),
                        $item->ProductVariationID
                    );
                }
            }
            $remove = CheckboxField::create($name . '[Remove]', _t('SilverShop\Generic.Remove', 'Remove'));
            $editables->push(
                $item->customise(
                    [
                        'QuantityField' => $quantity,
                        'VariationField' => $variationfield,
                        'RemoveField' => $remove,
                    ]
                )
            );
        }

        if (is_callable($this->editableItemsCallback)) {
            $callback = $this->editableItemsCallback;
            $editables = $callback($editables);
        }

        return $editables;
    }
}
