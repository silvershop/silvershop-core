<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\Control\RequestHandler;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Page\CartPageController;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Buyable;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\NumericField;

/**
 * @package shop
 */
class AddProductForm extends Form
{
    /**
     * Populates quantity dropdown with this many values
     */
    protected int $maxquantity = 0;

    /**
     * Fields that can be saved to an order item.
     */
    protected array $saveablefields = [];

    public function __construct(RequestHandler $requestHandler, string $name = "AddProductForm")
    {

        parent::__construct(
            $requestHandler,
            $name,
            $this->getFormFields($requestHandler),
            $this->getFormActions(),
            $this->getFormValidator()
        );

        $this->addExtraClass("silvershop-add-product-form");

        $this->extend('updateAddProductForm');
    }

    /**
     * Choose maximum value to populate quantity dropdown
     */
    public function setMaximumQuantity($qty): static
    {
        $this->maxquantity = (int)$qty;

        return $this;
    }

    public function setSaveableFields(array $fields): void
    {
        $this->saveablefields = $fields;
    }

    /**
     * The fields (if any) that a subclass has restricted the saveable order-item data to. An empty array means
     * the whole submitted data is saved. Exposed so an {@see prepareSaveableData()} hook can append a field to a
     * restricted set (e.g. per-line personalisation adding its identity hash to a bundle's selected items).
     *
     * @return array<int, string>
     */
    public function getSaveableFields(): array
    {
        return $this->saveablefields;
    }

    /**
     * Build the data saved onto the order item for this add. Fires the `updateAddToCartData` extension hook
     * first (by reference), so a module can fold extra submitted inputs into the line before the cart matches
     * it — the one place to transform add-to-cart data server-side, pre-add, for every product type. When a
     * subclass has restricted the saveable fields the result is limited to those (SQL-escaped); otherwise the
     * whole submitted data passes through unchanged, exactly as before.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareSaveableData(array $data, Buyable $buyable): array
    {
        $this->extend('updateAddToCartData', $data, $buyable);

        if ($this->saveablefields === []) {
            return $data;
        }

        return Convert::raw2sql(
            array_intersect_key($data, array_combine($this->saveablefields, $this->saveablefields))
        );
    }

    public function addtocart(array $data, $form)
    {
        if ($buyable = $this->getBuyable($data)) {
            $cart = ShoppingCart::singleton();
            $request = $this->getRequest();

            $order = $cart->current();
            if ($request && $request->isAjax() && $order) {
                ShopTools::install_locale($order->Locale);
            }

            $saveabledata = $this->prepareSaveableData($data, $buyable);

            $explicitZero = isset($data['Quantity']) && is_numeric($data['Quantity']) && (int)$data['Quantity'] === 0;
            $quantity = 0;
            if ($explicitZero) {
                $cart->remove($buyable, null, $saveabledata);
            } else {
                $quantity = isset($data['Quantity']) ? max(0, (int)$data['Quantity']) : 1;
                if (($this->maxquantity >0) && ($quantity > $this->maxquantity)) {
                    $quantity = $this->maxquantity;
                    $form->sessionMessage(
                        _t('SilverShop\Forms\AddProductForm.QuantitySetToMaximum', 'Set to maximum quantity'),
                        'good'
                    );
                }

                $cart->add($buyable, $quantity, $saveabledata);
            }

            if (!CartPageController::config()->direct_to_cart_page) {
                $form->SessionMessage($cart->getMessage(), $cart->getMessageType());
            }

            $this->extend('updateAddToCart', $form, $buyable);

            $this->extend('updateAddProductFormResponse', $request, $response, $buyable, $quantity, $form);

            return $response ? $response : CartPageController::direct($cart->getMessageType());
        }
    }

    public function getBuyable($data = null)
    {
        if ($this->controller->dataRecord instanceof Buyable) {
            return $this->controller->dataRecord;
        }

        return Product::get()->byID((int)$this->getRequest()->postVar('BuyableID'));
    }

    /**
     * @param RequestHandler $controller the controller instance that is being passed to the form
     */
    protected function getFormFields(RequestHandler $controller): FieldList
    {
        $fieldList = FieldList::create();

        if ($this->maxquantity !== 0) {
            $values = [];
            $count = 1;

            while ($count <= $this->maxquantity) {
                $values[$count] = $count;
                ++$count;
            }

            $fieldList->push(DropdownField::create('Quantity', _t('SilverShop\Generic.Quantity', 'Quantity'), $values, 1));
        } else {
            $fieldList->push(
                NumericField::create('Quantity', _t('SilverShop\Generic.Quantity', 'Quantity'), 1)
                    ->setAttribute('type', 'number')
                    ->setAttribute('min', '0')
                    ->setAttribute('step', '1')
            );
        }

        return $fieldList;
    }

    protected function getFormActions(): FieldList
    {
        return FieldList::create(
            FormAction::create('addtocart', _t("SilverShop\Page\Product.AddToCart", 'Add to Cart'))
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );
    }

    protected function getFormValidator()
    {
        return RequiredFieldsValidator::create(
            [
                'Quantity',
            ]
        );
    }
}
