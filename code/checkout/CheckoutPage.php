<?php
/**
 * CheckoutPage is a CMS page-type that shows the order
 * details to the customer for their current shopping
 * cart on the site.
 *
 * @see CheckoutPage_Controller->Order()
 *
 * @package shop
 */
class CheckoutPage extends Page {

	private static $db = array(
		'PurchaseComplete' => 'HTMLText'
	);

	private static $icon = 'shop/images/icons/money';

	/**
	 * Returns the link to the checkout page on this site
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	public static function find_link($urlSegment = false, $action = null, $id = null) {
		$base = CheckoutPage_Controller::config()->url_segment;
		if($page = self::get()->first()) {
			$base = $page->Link();
		}
		return Controller::join_links($base, $action, $id);
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', array(
			HtmlEditorField::create('PurchaseComplete', 'Purchase Complete', 4)
				->setDescription(
					"This message is included in reciept email, after the customer submits the checkout"
				)
		), 'Metadata');
		return $fields;
	}

	/**
	 * This module always requires a page model.
	 */
	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!self::get()->exists() && $this->config()->create_default_pages){
			$page = self::create(array(
				'Title' => 'Checkout',
				'URLSegment' => CheckoutPage_Controller::config()->url_segment,
				'ShowInMenus' => 0
			));
			$page->write();
			$page->publish('Stage', 'Live');
			$page->flushCache();
			DB::alteration_message('Checkout page created', 'created');
		}
	}

}

/**
 *  @package shop
 */
class CheckoutPage_Controller extends Page_Controller {

	private static $url_segment = 'checkout';
	private static $allowed_actions = array(
		'OrderForm',
		'payment',
		'PaymentForm'
	);

	public function Title() {
		if($this->Title) {
			return $this->Title;
		}

		return _t('CheckoutPage.TITLE', "Checkout");
	}

	public function OrderForm() {
		if(!(bool)$this->Cart()) {
			return false;
		}

		/** @var CheckoutComponentConfig $config */
		$config = Injector::inst()->create("CheckoutComponentConfig", ShoppingCart::curr());
		$form = new PaymentForm($this, 'OrderForm', $config);

		// Normally, the payment is on a second page, either offsite or through /checkout/payment
		// If the site has customised the checkout component config to include an onsite payment
		// component, we should honor that and change the button label. PaymentForm::checkoutSubmit
		// will also check this and process payment if needed.
		if ($config->getComponentByType('OnsitePaymentCheckoutComponent')) {
			$form->setActions(new FieldList(
				FormAction::create('checkoutSubmit', _t('CheckoutForm.SubmitPayment', 'Submit Payment'))
			));
		}

		$form->Cart = $this->Cart();
		$this->extend('updateOrderForm', $form);

		return $form;
	}

	/**
	 * Action for making on-site payments
	 */
	public function payment() {
		if(!$this->Cart()) {
			return $this->redirect($this->Link());
		}

		return array(
			'Title' => 'Make Payment',
			'OrderForm' => $this->PaymentForm()
		);
	}

	public function PaymentForm() {
		if(!(bool) $this->Cart()) {
			return false;
		}

		$config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
		$config->AddComponent(new OnsitePaymentCheckoutComponent());

		$form = PaymentForm::create($this, "PaymentForm", $config);
		$form->setActions(new FieldList(
			FormAction::create("submitpayment", _t('CheckoutForm.SubmitPayment', 'Submit Payment'))
		));
		$form->setFailureLink($this->Link());
		$this->extend('updatePaymentForm', $form);

		return $form;
	}

	/**
	 * Retrieves error messages for the latest payment (if existing).
	 * This can originate e.g. from an earlier offsite gateway API response.
	 * 
	 * @return string
	 */
	public function PaymentErrorMessage() {
		$order = $this->Cart();
		if(!$order) return false;

		$lastPayment = $order->Payments()->sort('Created', 'DESC')->first();
		if(!$lastPayment) return false;

		$errorMessages = $lastPayment->Messages()->exclude('Message', '')->sort('Created', 'DESC');
		$lastErrorMessage = null;
		foreach($errorMessages as $errorMessage) {
			if($errorMessage instanceof GatewayErrorMessage) {
				$lastErrorMessage = $errorMessage;
				break;
			}
		}
		if(!$lastErrorMessage) return false;

		return $lastErrorMessage->Message;
	}
}
