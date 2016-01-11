<?php
/**
 * Renders the cart inside a form, so that it is editable.
 *
 * @package shop
 */
class CartForm extends Form{

	protected $cart;

	public function __construct($controller, $name = "CartForm", $cart = null, $template = "Cart") {
		$this->cart = $cart;
		$fields = new FieldList(
			CartEditField::create("Items","",$this->cart)
				->setTemplate($template)
		);
		$actions = new FieldList(
			FormAction::create("updatecart", _t('CartForm.UpdateCart', "Update Cart"))
		);

		parent::__construct($controller, $name, $fields, $actions);
	}

	/**
	 * Update the cart using data collected
	 */
	public function updatecart($data, $form) {
		$items = $this->cart->Items();
		$updatecount = $removecount = 0;

        $request = $this->getRequest();
        $order = ShoppingCart::curr();
        if($request && $request->isAjax() && $order){
            ShopTools::install_locale($order->Locale);
        }

		$messages = array();
		if(isset($data['Items']) && is_array($data['Items'])){
			foreach($data['Items'] as $itemid => $fields){
				$item = $items->byID($itemid);
				if(!$item){
					continue;
				}
				//delete lines
				if(isset($fields['Remove']) || (isset($fields['Quantity']) && (int)$fields['Quantity'] <= 0)){
					$items->remove($item);
					$removecount++;
					continue;
				}
				//update quantities
				if(isset($fields['Quantity']) && $quantity = Convert::raw2sql($fields['Quantity'])){
					$item->Quantity = $quantity;
				}
				//update variations
				if(isset($fields['ProductVariationID']) && $id = Convert::raw2sql($fields['ProductVariationID'])){
					if($item->ProductVariationID != $id){
						$item->ProductVariationID = $id;
					}
				}
				//TODO: make updates through ShoppingCart class
				//TODO: combine with items that now match exactly
				//TODO: validate changes
				if($item->isChanged()){
					$item->write();
					$updatecount++;
				}
			}
		}
		if($removecount){
			$messages['remove'] = _t(
				'CartForm.REMOVED_ITEMS',
				"Removed {count} items.",
				"count is the amount that was removed",
				array('count' => $removecount)
			);
		}
		if($updatecount){
			$messages['updatecount'] = _t(
				'CartForm.UPDATED_ITEMS',
				"Updated {count} items.",
				"count is the amount that was updated",
				array('count' => $updatecount)
			);
		}
		if(count($messages)){
			$form->sessionMessage(implode(" ", $messages), "good");
		}


		$this->extend('updateCartFormResponse', $request, $response, $form);

		return $response ? $response : $this->controller->redirectBack();
	}

}
