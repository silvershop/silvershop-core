<?php

class CheckoutComponentConfig {

	protected $components;
	protected $order;
	protected $namespaced; //namespace fields according to their component

	public function __construct(Order $order, $namespaced = true) {
		$this->components = new ArrayList();
		$this->order = $order;
		$this->namespaced = $namespaced;
	}

	public function getOrder() {
		return $this->order;
	}

	/**
	 * @param CheckoutComponent $component
	 * @param string $insertBefore The class of the component to insert this one before
	 */
	public function addComponent(CheckoutComponent $component, $insertBefore = null) {
		if($this->namespaced){
			$component = new CheckoutComponent_Namespaced($component);
		}
		if($insertBefore) {
			$existingItems = $this->getComponents();
			$this->components = new ArrayList;
			$inserted = false;
			foreach($existingItems as $existingItem) {
				if(!$inserted && $existingItem instanceof $insertBefore) {
					$this->components->push($component);
					$inserted = true;
				}
				$this->components->push($existingItem);
			}
			if(!$inserted) $this->components->push($component);
		} else {
			$this->getComponents()->push($component);
		}
		return $this;
	}

	/**
	 * @return ArrayList Of GridFieldComponent
	 */
	public function getComponents() {
		if(!$this->components) {
			$this->components = new ArrayList();
		}
		return $this->components;
	}

	/**
	 * Returns the first available component with the given class or interface.
	 *
	 * @param String ClassName
	 * @return GridFieldComponent
	 */
	public function getComponentByType($type) {
		foreach($this->components as $component) {
			if($this->namespaced){
				if($component->Proxy() instanceof $type) return $component->Proxy();
			}else{
				if($component instanceof $type) return $component;
			}
		}
	}

	/**
	 * Get combined form fields
	 * @return FieldList namespaced fields
	 */
	public function getFormFields() {
		$fields = new FieldList();
		foreach($this->getComponents() as $component) {
			if($cfields = $component->getFormFields($this->order)) {
				$fields->merge($cfields);
			}else{
				user_error("getFields on  ".get_class($component)." must return a FieldList");
			}
		}
		return $fields;
	}

	public function getRequiredFields() {
		$required = array();
		foreach($this->getComponents() as $component) {
			$required = array_merge($required, $component->getRequiredFields($this->order));
		}
		return $required;
	}

	/**
	 * Validate every component against given data.
	 * @param  array $data data to validate
	 * @return boolean validation result
	 * @throws ValidationException
	 */
	public function validateData($data) {
		$result = new ValidationResult();
		foreach($this->getComponents() as $component){
			try{
				$component->validateData($this->order, $this->dependantData($component, $data));
			}catch(ValidationException $e){
				//transfer messages into a single result
				foreach($e->getResult()->messageList() as $code => $message){
					if(is_numeric($code)){
						$code = null;
					}
					if($this->namespaced){
						$code = $component->namespaceFieldName($code);
					}
					$result->error($message, $code);
				}
			}
		}
		if(!$result->valid()){
			throw new ValidationException($result);
		}

		return true;
	}

	/**
	 * Get combined data
	 * @return array map of field names to data values
	 */
	public function getData() {
		$data = array();

		foreach($this->getComponents() as $component) {
			$orderdata = $component->getData($this->order);

			if(is_array($orderdata)) {
				$data = array_merge($data, $orderdata);
			} else {
				user_error("getData on  ".$component->name()." must return an array");
			}
		}
		return $data;
	}

	/**
	 * Set data on all components
	 * @param array $data map of field names to data values
	 */
	public function setData($data) {
		foreach($this->getComponents() as $component){
			$component->setData($this->order, $this->dependantData($component, $data));
		}
	}

	/**
	 * Helper function for saving data from other components.
	 */
	protected function dependantData($component, $data) {
		if(!$this->namespaced){ //no need to try and get un-namespaced dependant data
			return $data;
		}
		$dependantdata = array();
		foreach($component->dependsOn() as $dependanttype){
			$dependant = null;
			foreach($this->components as $check){
				if(get_class($check->Proxy()) == $dependanttype){
					$dependant = $check;
					break;
				}
			}
			if(!$dependant){
				user_error("Could not find a $dependanttype component, as depended by ".$component->name());
			}
			$dependantdata = array_merge(
				$dependantdata,
				$component->namespaceData($dependant->unnamespaceData($data))
			);
		}
		return array_merge($dependantdata, $data);
	}

}

class SinglePageCheckoutComponentConfig extends CheckoutComponentConfig {

	public function __construct(Order $order) {
		parent::__construct($order);
		$this->addComponent(new CustomerDetailsCheckoutComponent());
		$this->addComponent(new ShippingAddressCheckoutComponent());
		$this->addComponent(new BillingAddressCheckoutComponent());
		if(Checkout::member_creation_enabled() && !Member::currentUserID()){
			$this->addComponent(new MembershipCheckoutComponent());
		}
		if(count(GatewayInfo::get_supported_gateways()) > 1){
			$this->addComponent(new PaymentCheckoutComponent());
		}
		$this->addComponent(new NotesCheckoutComponent());
		$this->addComponent(new TermsCheckoutComponent());
	}

}
