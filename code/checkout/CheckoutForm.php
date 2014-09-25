<?php

class CheckoutForm extends Form {

	protected $config;
	
	protected $redirectlink;

	public function __construct($controller, $name, CheckoutComponentConfig $config) {
		$this->config = $config;
		$fields = $config->getFormFields();
		$actions = new FieldList(
			FormAction::create(
				'checkoutSubmit',
				_t('CheckoutForm', 'Proceed to payment')
			)
	);
		$validator = new CheckoutComponentValidator($this->config);
		parent::__construct($controller, $name, $fields, $actions, $validator);
		//load data from various sources
		$this->loadDataFrom($this->config->getData(), Form::MERGE_IGNORE_FALSEISH);
		if($member = Member::currentUser()) {
			$this->loadDataFrom($member, Form::MERGE_IGNORE_FALSEISH);
		}
		if($sessiondata = Session::get("FormInfo.{$this->FormName()}.data")){
			$this->loadDataFrom($sessiondata, Form::MERGE_IGNORE_FALSEISH);
		}
	}

	public function setRedirectLink($link) {
		$this->redirectlink = $link;
	}

	public function checkoutSubmit($data, $form) {
		//form validation has passed by this point, so we can save data
		$this->config->setData($form->getData());
		if($this->redirectlink) {

			return $this->controller->redirect($this->redirectlink);
		}

		return $this->controller->redirectBack();
	}

	public function getConfig(){
		return $this->config;
	}

}
