<?php

class CartForm extends Form{

	protected $cart;

	public function __construct($controller, $name = "CartForm", $cart, $template = "Cart"){
		$this->cart = $cart;
		parent::__construct($controller, $name, new FieldList(
			LiteralField::create("cartcontent",
				SSViewer::execute_template($template, $cart->customise(array(
					'Items' => $this->editableItems($cart->Items())
				)),array(
					'Editable' => true
				))
			)
		),new FieldList(
			FormAction::create("updatecart","Update Cart")
		));
	}
	
	public function editableItems($items){
		$editables = new ArrayList();
		$i = Injector::inst();
		foreach($items as $item){
			$name = "Item[$item->ID]";
			$quantity = NumericField::create($name."[Quantity]","Quantity",$item->Quantity);
			$variation = false;
			$variations = $item->Product()->Variations();
			if($variations->exists()){
				$variation = DropdownField::create(
					$name."[ProductVariationID]",
					"Varaition",
					$variations->map('ID','Title'),
					$item->ProductVariationID
				);
			}
			$remove = CheckboxField::create($name."[Remove]","Remove");
			$editables->push($item->customise(array(
				"QuantityField" => $quantity,
				"VariationField" => $variation,
				"RemoveField" => $remove 
			)));
		}

		return $editables;
	}

	public function updatecart($data, $form){
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
				if(isset($fields['Remove'])){
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
					$item->ProductVariationID = $id;
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
			$messages['remove'] = "Removed ".$updatecount." items.";
		}
		if($updatecount){
			$messages['updatecount'] = "Updated ".$updatecount." items.";
		}
		if(count($messages)){
			$form->sessionMessage(implode(" ", $messages),"good");
		}		
		$this->controller->redirectBack();
	}

}