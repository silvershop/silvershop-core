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
		$fields = new FieldList();
		//only render the cart if it exists
		if($this->cart){
			$fields->push(LiteralField::create("cartcontent",
				$this->getCartContent($template)
			));
		}

		$actions = new FieldList(
			FormAction::create("updatecart", "Update Cart")
		);

		parent::__construct($controller, $name, $fields, $actions);
	}

	/**
	 * Add quantity, variation and remove fields to the
	 * item set.
	 * This method is static so that it can be used externally.
	 * @param SS_List $items
	 */
	public static function add_edit_fields(SS_List $items) {
		$editables = new ArrayList();
		foreach($items as $item){
			$buyable = $item->Product();
			if(!$buyable){
				continue;
			}
			$name = "Item[$item->ID]";
			$quantity = NumericField::create($name."[Quantity]", "Quantity", $item->Quantity);
			$variationfield = false;
			if($buyable->has_many("Variations")){
				$variations = $buyable->Variations();
				if($variations->exists()){
					$variationfield = DropdownField::create(
						$name."[ProductVariationID]",
						"Varaition",
						$variations->map('ID', 'Title'),
						$item->ProductVariationID
					);
				}
			}
			$remove = CheckboxField::create($name."[Remove]", "Remove");
			$editables->push($item->customise(array(
				"QuantityField" => $quantity,
				"VariationField" => $variationfield,
				"RemoveField" => $remove
			)));
		}

		return $editables;
	}

	/**
	 * Get the HTML cart content to add inside the form
	 * @param  string $template
	 * @return string rendered content
	 */
	protected function getCartContent($template) {
		return SSViewer::execute_template(
			$template,
			$this->cart->customise(array(
				'Items' => self::add_edit_fields($this->cart->Items())
			)), array(
				'Editable' => true
			)
		);
	}

	/**
	 * Update the cart using data collected
	 */
	public function updatecart($data, $form) {
		$items = $this->cart->Items();
		$updatecount = $removecount = 0;
		$messages = array();
		if(isset($data['Item']) && is_array($data['Item'])){
			foreach($data['Item'] as $itemid => $fields){
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
			$messages['remove'] = "Removed ".$removecount." items.";
		}
		if($updatecount){
			$messages['updatecount'] = "Updated ".$updatecount." items.";
		}
		if(count($messages)){
			$form->sessionMessage(implode(" ", $messages), "good");
		}
		$this->controller->redirectBack();
	}

}
