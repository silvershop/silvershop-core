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
	 * @param  Order  $order [description]
	 * @return [type]        [description]
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

	//namespace wrappers - so form fields don't conflict.
	//note: component subclasses shouldn't need to use any of these functions
		//..so perhaps this can all be moved elsewhere

	public function getNamespacedFormFields(Order $order) {
		$fields = $this->getFormFields($order);
		foreach($fields as $field){
			$field->setName($this->namespaceFieldName($field->getName()));
		}
		return $fields;
	}

	public function getNamespacedRequiredFields(Order $order) {
		$fields = $this->getRequiredFields($order);
		$namespaced = array();
		foreach($fields as $field){
			$namespaced[] = $this->namespaceFieldName($field);
		}
		return $namespaced;
	}

	public function getNamespacedData(Order $order) {
		return $this->namespaceData($this->getData($order));
	}

	public function setNamespacedData(Order $order, array $data) {
		$this->setData($order, $this->unnamespaceData($data));
	}

	public function namespaceData(array $data){
		$newdata = array();
		foreach($data as $key => $value){
			$newdata[$this->namespaceFieldName($key)] = $value;
		}
		return $newdata;
	}

	public function unnamespaceData(array $data){
		$newdata = array();
		foreach($data as $key => $value){
			if(strpos($key, get_class($this)) === 0){
				$newdata[$this->unnamespaceFieldName($key)] = $value;
			}
		}
		return $newdata;
	}

	public function namespaceFieldName($name){
		return get_class($this)."_".$name;
	}

	public function unnamespaceFieldName($name){
		return substr($name,strlen(get_class($this)."_"));
	}

}