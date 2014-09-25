<?php
/**
 * CheckoutComponent
 *
 * A modularised piece of checkout functionality.
 *
 * A checkout component will:
 *
 *  - provide form fields
 *  - validate entered data
 *  - save data from given form fields
 *
 */
abstract class CheckoutComponent {

	protected $requiredfields = array();
	protected $dependson = array();

	/**
	 * Get form fields for manipulating the current order,
	 * according to the responsibilty of this component.
	 *
	 * @param  Form $form the form being updated
	 * @throws Exception
	 * @return FieldList fields for manipulating order
	 */
	abstract public function getFormFields(Order $order);

	/**
	 * Is this data valid for saving into an order?
	 *
	 * This function should never rely on form.
	 *
	 * @param array $data data to be validated
	 * @throws ValidationException
	 * @return boolean the data is valid
	 */
	abstract public function validateData(Order $order, array $data);

	/**
	 * Get required data out of the model.
	 * @param  Order  $order order to get data from.
	 * @return array        get data from model(s)
	 */
	abstract public function getData(Order $order);

	/**
	 * Set the model data for this component.
	 *
	 * This function should never rely on form.
	 *
	 * @param array $data data to be saved into order object
	 * @throws Exception
	 * @return Order the updated order
	 */
	abstract public function setData(Order $order, array $data);

	/**
	 * Get the data fields that are required for the component.
	 * @param  Order  $order [description]
	 * @return array        required data fields
	 */
	public function getRequiredFields(Order $order) {
		return $this->requiredfields;
	}

	public function dependsOn() {
		return $this->dependson;
	}

	public function name() {
		return get_class($this);
	}

}

/**
 * Proxy class to handle namespacing field names for checkout components
 */
class CheckoutComponent_Namespaced extends CheckoutComponent {

	protected $proxy;

	public function __construct(CheckoutComponent $component) {
		$this->proxy = $component;
	}

	public function Proxy() {
		return $this->proxy;
	}

	public function getFormFields(Order $order) {
		$fields = $this->proxy->getFormFields($order);
		$allFields = $fields->dataFields();
		if($allFields) {
			foreach($allFields as $field){
				$field->setName($this->namespaceFieldName($field->getName()));
			}	
		}
		
		return $fields;
	}

	public function validateData(Order $order, array $data) {
		return $this->proxy->validateData($order, $this->unnamespaceData($data));
	}

	public function getData(Order $order) {
		return $this->namespaceData($this->proxy->getData($order));
	}

	public function setData(Order $order, array $data) {
		return $this->proxy->setData($order, $this->unnamespaceData($data));
	}

	public function getRequiredFields(Order $order) {
		$fields = $this->proxy->getRequiredFields($order);
		$namespaced = array();
		foreach($fields as $field){
			$namespaced[] = $this->namespaceFieldName($field);
		}
		return $namespaced;
	}

	public function dependsOn() {
		return $this->proxy->dependsOn();
	}

	public function name() {
		return $this->proxy->name();
	}

	//namespacing functions

	public function namespaceData(array $data) {
		$newdata = array();
		foreach($data as $key => $value){
			$newdata[$this->namespaceFieldName($key)] = $value;
		}
		return $newdata;
	}

	public function unnamespaceData(array $data) {
		$newdata = array();
		foreach($data as $key => $value){
			if(strpos($key, $this->name()) === 0){
				$newdata[$this->unnamespaceFieldName($key)] = $value;
			}
		}
		return $newdata;
	}

	public function namespaceFieldName($name) {
		return $this->name()."_".$name;
	}

	public function unnamespaceFieldName($name) {
		return substr($name, strlen($this->name()."_"));
	}

}
