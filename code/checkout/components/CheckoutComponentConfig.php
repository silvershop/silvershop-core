<?php

class CheckoutComponentConfig {
	
	/**
	 * @var ArrayList
	 */
	protected $components = null;

	protected $order;

	/**
	 * 
	 */
	public function __construct(Order $order) {
		$this->order = $order;
		$this->components = new ArrayList();
	}

	public function getOrder(){
		return $this->order;
	}

	/**
	 * @param CheckoutComponent $component 
	 * @param string $insertBefore The class of the component to insert this one before
	 */
	public function addComponent(CheckoutComponent $component, $insertBefore = null) {
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
	 * Returns all components extending a certain class, or implementing a certain interface.
	 * 
	 * @param String Class name or interface
	 * @return ArrayList Of GridFieldComponent
	 */
	public function getComponentsByType($type) {
		$components = new ArrayList();
		foreach($this->components as $component) {
			if($component instanceof $type) $components->push($component);
		}
		return $components;
	}

	/**
	 * Returns the first available component with the given class or interface.
	 * 
	 * @param String ClassName
	 * @return GridFieldComponent
	 */
	public function getComponentByType($type) {
		foreach($this->components as $component) {
			if($component instanceof $type) return $component;
		}
	}

	/**
	 * Get combined form fields
	 * @return FieldList namespaced fields
	 */
	public function getFormFields($namespaced = true) {
		$fields = new FieldList();
		foreach($this->getComponents() as $component) {
			$cfields = $namespaced ?
						$component->getNamespacedFormFields($this->order) :
						$component->getFormFields($this->order);
			if($cfields){
				$fields->merge($cfields);
			}else{
				user_error("getFields on  ".get_class($component)." must return a FieldList");
			}
		}
		return $fields;
	}

	public function getRequiredFields($namespaced = true) {
		$required = array();
		foreach($this->getComponents() as $component) {
			$fields = $namespaced ?
						$component->getNamespacedRequiredFields($this->order) :
						$component->getRequiredFields($this->order);
			$required = array_merge($required, $fields);
		}
		return $required;
	}

	/**
	 * Validate every component against given data.
	 * @param  array $data data to validate
	 * @return boolean validation result
	 * @throws ValidationException
	 */
	public function validateData($data, $namespaced = true) {
		$result = new ValidationResult();
		foreach($this->getComponents() as $component){
			try{
				$component->validateData($this->order, $component->unnamespaceData($data));
			}catch(ValidationException $e){
				//transfer messages into a single result
				foreach($e->getResult()->messageList() as $code => $message){
					if(is_numeric($code)){
						$code = null;
					}
					if($namespaced){
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
	 * @param  boolean $namespaced [description]
	 * @return [type]              [description]
	 */
	public function getData($namespaced = true) {
		$data = array();
		foreach($this->getComponents() as $component) {
			$orderdata = $namespaced ?
				$component->getNamespacedData($this->order) :
				$component->getData($this->order);
			if(is_array($orderdata)){
				$data = array_merge($data, $orderdata);
			}else{
				user_error("getData on  ".get_class($component)." must return an array");
			}
		}

		return $data;
	}

	public function setData($data, $namespaced = true){
		foreach($this->getComponents() as $component){
			$namespaced ?
				$component->setNamespacedData($this->order, $data) :
				$component->setData($this->order, $data);
		}
	}

}

class SinglePageCheckoutComponentConfig extends CheckoutComponentConfig {

	public function __construct(Order $order){
		parent::__construct($order);
		$this->addComponent(new CustomerDetailsCheckoutComponent());
		$this->addComponent(new ShippingAddressCheckoutComponent());
		$this->addComponent(new BillingAddressCheckoutComponent());
		if(Checkout::member_creation_enabled()){
			$this->addComponent(new MembershipCheckoutComponent());
		}
		if(count(GatewayInfo::get_supported_gateways()) > 1){
			$this->addComponent(new PaymentCheckoutComponent());
		}
		$this->addComponent(new NotesCheckoutComponent());
		$this->addComponent(new TermsCheckoutComponent());
	}

}