<?php

/**
 * @package shop
 */
class CheckoutForm extends Form {

	protected $config;
	
	protected $redirectlink;

	private $checkout_action_text = 'Proceed to payment';

	public function __construct($controller, $name, CheckoutComponentConfig $config) {
		$this->config = $config;
		$fields = $config->getFormFields();

		$actions = new FieldList(
			FormAction::create(
				'checkoutSubmit',
				_t('CheckoutForm.PROCEED', $this->config()->get('checkout_action_text'))
			)
		);

		$validator = new CheckoutComponentValidator($this->config);
		
		// For single country sites, the Country field is readonly therefore no need to validate
		if(SiteConfig::current_site_config()->getSingleCountry()){
			$validator->removeRequiredField("ShippingAddressCheckoutComponent_Country");
			$validator->removeRequiredField("BillingAddressCheckoutComponent_Country");
		}

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

	/**
	 * @param array $data
	 * @param Form $form
	 *
	 * @return redirect
	 */
	public function checkoutSubmit($data, $form) {
		// form validation has passed by this point, so we can save data
		$this->config->setData($form->getData());

		if($this->redirectlink) {
			return $this->controller->redirect($this->redirectlink);
		}

		// if no redirect link provided (i.e no payment step) then we need to
		// process the order
		$orderProcessor = Injector::inst()->create('OrderProcessor', $this->config->getOrder());

		if(!$orderProcessor->placeOrder()) {
			$form->sessionMessage($orderProcessor->getError());

			return $this->controller->redirectBack();
		}

		// order is complete, no other steps.
		return $this->controller->redirect(AccountPage::find_link());
	}

	/**
	 *
	 */
	public function getConfig() {
		return $this->config;
	}

}
