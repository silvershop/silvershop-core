<?php
/**
 * Field for editing cart/items within a form
 *
 * @package shop
 */
class CartEditField extends FormField{

	protected $cart;
	protected $items;
	protected $template = "Cart";

	protected $editableItemsCallback;

	public function __construct($name, $title, $cart) {
		parent::__construct($name, $title);
		$this->cart = $cart;
		$this->items = $cart->Items();
	}

	/**
	 * Set tempalte for rendering editable cart.
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
		return $this;
	}


	/**
	 * Allow overriding the given items list.
	 * This helps with formatting, grouping, ordering etc.
	 */
	public function setItemsList(SS_List $list){
		$this->items = $list;
		return $this;
	}

	/**
	 * Get the items list being used to produce the cart.
	 * @return SS_List
	 */
	public function getItemsList(){
		return $this->items;
	}

	/**
	 * Provides a way to modify the editableItems list
	 * before it is rendered.
	 * @param Closure $callback
	 */
	public function setEditableItemsCallback(Clousre $callback){
		$this->editableItemsCallback = $callback;
	}

	/**
	 * Render the cart with editable item fields.
	 * @param array $properties
	 */
	public function Field($properties = array()) {
		$editables = $this->editableItems();
		$customcartdata = array(
			'Items' => $editables
		);
		$this->extend('onBeforeRender', $editables, $customcartdata);

		return SSViewer::execute_template(
			$this->template,
			$this->cart->customise($customcartdata),
			array('Editable' => true)
		);
	}

	/**
	 * Add quantity, variation and remove fields to the
	 * item set.
	 * @param SS_List $items
	 */
	protected function editableItems() {
		$editables = new ArrayList();
		foreach($this->items as $item){
			$buyable = $item->Product();
			if(!$buyable){
				continue;
			}
			$name = $this->name."[$item->ID]";
			$quantity = NumericField::create(
				$name."[Quantity]", "Quantity", $item->Quantity
			);
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

		if(is_callable($this->editableItemsCallback)){
			$callback = $this->editableItemsCallback;
			$editables = $callback($editables);
		}

		return $editables;
	}

}