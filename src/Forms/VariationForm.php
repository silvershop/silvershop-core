<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Control\RequestHandler;
use Exception;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Page\CartPageController;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * @package shop
 */
class VariationForm extends AddProductForm
{
    private static bool $include_json = true;

    protected array $requiredFields = ['Quantity'];

    public function __construct(RequestHandler $requestHandler, string $name = 'VariationForm')
    {
        parent::__construct($requestHandler, $name);
        $this->extend('updateVariationForm');
    }

    /**
     * Adds a given product to the cart. If a hidden field is passed
     * (ValidateVariant) then simply a validation of the user including that
     * product is done and the users cart isn't actually changed.
     *
     * @param  array $data
     * @param  Form  $form
     * @return mixed
     */
    public function addtocart($data, $form)
    {
        $quantity = null;
        if ($variation = $this->getBuyable($data)) {
            $cart = ShoppingCart::singleton();
            $request = $this->getRequest();

            $order = $cart->current();
            if ($request && $request->isAjax() && $order) {
                ShopTools::install_locale($order->Locale);
            }

            // if we are in just doing a validation step then check
            if ($this->getRequest()->requestVar('ValidateVariant')) {
                $message = '';
                $success = false;

                try {
                    $success = $variation->canPurchase(null, $data['Quantity']);
                } catch (Exception $e) {
                    $message = get_class($e);
                    // added hook to update message
                    $this->extend('updateVariationAddToCartMessage', $e, $message, $variation);
                }

                $ret = [
                    'Message' => $message,
                    'Success' => $success,
                    'Price' => $variation->dbObject('Price')->TrimCents(),
                ];

                $this->extend('updateVariationAddToCartAjax', $ret, $variation, $form);

                return json_encode($ret);
            }

            $saveabledata = ($this->saveablefields !== []) ? Convert::raw2sql(
                array_intersect_key($data, array_combine($this->saveablefields, $this->saveablefields))
            ) : $data;

            $explicitZero = isset($data['Quantity']) && is_numeric($data['Quantity']) && (int)$data['Quantity'] === 0;
            $quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? max(0, (int)$data['Quantity']) : 1;

            $ok = $explicitZero
                ? $cart->remove($variation, null, $saveabledata)
                : $cart->add($variation, $quantity, $saveabledata);

            if ($ok) {
                $form->sessionMessage(
                    $explicitZero
                        ? $cart->getMessage()
                        : _t('SilverShop\Cart\ShoppingCart.ItemAdded', 'Item has been added successfully.'),
                    'good'
                );
            } else {
                $form->sessionMessage($cart->getMessage(), $cart->getMessageType());
            }
        } else {
            $variation = null;
            $form->sessionMessage(
                _t(__CLASS__ . '.VariationNotAvailable', 'That variation is not available, sorry.'),
                'bad'
            ); //validation fail
        }

        $this->extend('updateVariationAddToCart', $form, $variation);

        $this->extend('updateVariationFormResponse', $request, $response, $variation, $quantity, $form);
        return $response ? $response : CartPageController::direct();
    }

    public function getBuyable($data = null)
    {
        if (isset($data['ProductAttributes'])
            && $variation = $this->getController()->getVariationByAttributes($data['ProductAttributes'])
        ) {
            return $variation;
        }

        return null;
    }

    protected function getFormFields(RequestHandler $controller): FieldList
    {
        $fieldList = parent::getFormFields($controller);

        if (!$controller instanceof RequestHandler) {
            return $fieldList;
        }

        $product = $controller->data();
        $attributes = $product->VariationAttributeTypes();

        foreach ($attributes as $attribute) {
            $attributeDropdown = $attribute->getDropDownField(
                _t(
                    __CLASS__ . '.ChooseAttribute',
                    'Choose {attribute} …',
                    '',
                    ['attribute' => $attribute->Label]
                ),
                $product->possibleValuesForAttributeType($attribute)->sort(['Sort' => 'ASC'])
            );

            if ($attributeDropdown) {
                $fieldList->push($attributeDropdown);
                $this->requiredFields[] = sprintf('ProductAttributes[%s]', $attribute->ID);
            }
        }

        if ($this->config()->include_json) {
            $vararray = [];
            $query = new SQLSelect();
            $sqlSelect = $query;

            $query->setSelect('ID')
                ->setFrom('SilverShop_Variation')
                ->addWhere(['"ProductID" = ?' => $product->ID]);

            if (!Product::config()->allow_zero_price) {
                $query->addWhere('"Price" > 0');
            }

            foreach ($query->execute()->column('ID') as $variationID) {
                $sqlSelect->setSelect('SilverShop_AttributeValueID')
                    ->setFrom('SilverShop_Variation_AttributeValues')
                    ->setWhere(['SilverShop_VariationID' => $variationID]);
                $vararray[$variationID] = $sqlSelect->execute()->keyedColumn();
            }

            $fieldList->push(
                HiddenField::create(
                    'VariationOptions',
                    'VariationOptions',
                    json_encode($vararray)
                )
            );
        }

        return $fieldList;
    }

    protected function getFormValidator()
    {
        return VariationFormValidator::create(
            array_unique($this->requiredFields)
        );
    }
}
