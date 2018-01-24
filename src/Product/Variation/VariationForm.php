<?php

namespace SilverShop\Core\Product\Variation;


use SilverStripe\Core\Convert;
use SilverStripe\Forms\HiddenField;
use Exception;
use SQLQuery;


/**
 * @package shop
 */
class VariationForm extends AddProductForm
{
    public static $include_json = true;

    protected $requiredFields = ['Quantity'];

    public function __construct($controller, $name = "VariationForm")
    {
        parent::__construct($controller, $name);
        $this->extend('updateVariationForm');
    }

    /**
     * Adds a given product to the cart. If a hidden field is passed
     * (ValidateVariant) then simply a validation of the user including that
     * product is done and the users cart isn't actually changed.
     *
     * @return mixed
     */
    public function addtocart($data, $form)
    {
        if ($variation = $this->getBuyable($data)) {
            $quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int)$data['Quantity'] : 1;
            $cart = ShoppingCart::singleton();
            $request = $this->getRequest();

            $order = $cart->current();
            if ($request && $request->isAjax() && $order) {
                ShopTools::install_locale($order->Locale);
            }

            // if we are in just doing a validation step then check
            if ($this->request->requestVar('ValidateVariant')) {
                $message = '';
                $success = false;

                try {
                    $success = $variation->canPurchase(null, $data['Quantity']);
                } catch (Exception $e) {
                    $message = get_class($e);
                    // added hook to update message
                    $this->extend('updateVariationAddToCartMessage', $e, $message, $variation);
                }

                $ret = array(
                    'Message' => $message,
                    'Success' => $success,
                    'Price'   => $variation->dbObject('Price')->TrimCents(),
                );

                $this->extend('updateVariationAddToCartAjax', $ret, $variation, $form);

                return json_encode($ret);
            }

            $saveabledata = (!empty($this->saveablefields)) ? Convert::raw2sql(
                array_intersect_key($data, array_combine($this->saveablefields, $this->saveablefields))
            ) : $data;

            if ($cart->add($variation, $quantity, $saveabledata)) {
                $form->sessionMessage(
                    _t('ShoppingCart.ItemAdded', "Item has been added successfully."),
                    "good"
                );
            } else {
                $form->sessionMessage($cart->getMessage(), $cart->getMessageType());
            }
        } else {
            $variation = null;
            $form->sessionMessage(
                _t('VariationForm.VariationNotAvailable', "That variation is not available, sorry."),
                "bad"
            ); //validation fail
        }

        $this->extend('updateVariationAddToCart', $form, $variation);

        $this->extend('updateVariationFormResponse', $request, $response, $variation, $quantity, $form);
        return $response ? $response : ShoppingCart_Controller::direct();
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

    protected function getFormFields($controller = null)
    {
        $fields = parent::getFormFields($controller);

        if (!$controller) {
            return $fields;
        }
        $product = $controller->data();
        $attributes = $product->VariationAttributeTypes();

        foreach ($attributes as $attribute) {
            $attributeDropdown = $attribute->getDropDownField(
                _t(
                    'VariationForm.ChooseAttribute',
                    "Choose {attribute} …",
                    '',
                    array('attribute' => $attribute->Label)
                ),
                $product->possibleValuesForAttributeType($attribute)
            );

            if($attributeDropdown){
                $fields->push($attributeDropdown);
                $this->requiredFields[] = "ProductAttributes[$attribute->ID]";
            }
        }

        if (self::$include_json) {
            $vararray = array();

            $query = $query2 = new SQLQuery();

            $query->setSelect('ID')
                ->setFrom('ProductVariation')
                ->addWhere(array('ProductID' => $product->ID));

            if (!Product::config()->allow_zero_price) {
                $query->addWhere('"Price" > 0');
            }

            foreach ($query->execute()->column('ID') as $variationID) {
                $query2->setSelect('ProductAttributeValueID')
                    ->setFrom('ProductVariation_AttributeValues')
                    ->setWhere(array('ProductVariationID' => $variationID));
                $vararray[$variationID] = $query2->execute()->keyedColumn();
            }

            $fields->push(
                HiddenField::create(
                    'VariationOptions',
                    'VariationOptions',
                    json_encode($vararray)
                )
            );
        }

        return $fields;
    }

    protected function getFormValidator()
    {
        return VariationFormValidator::create(
            array_unique($this->requiredFields)
        );
    }
}