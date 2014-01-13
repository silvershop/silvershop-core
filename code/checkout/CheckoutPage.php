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
	static function find_link($urlSegment = false, $action = null, $id = null) {
		if(!$page = CheckoutPage::get()->first()) {
			return Controller::join_links(Director::baseURL(), CheckoutPage_Controller::config()->url_segment);
		}
		$id = ($id)? "/".$id : "";
		return ($urlSegment) ? $page->URLSegment : Controller::join_links($page->Link($action),$id);
	}

	/**
	 * Only allow one checkout page
	 */
	function canCreate($member = null) {
		return !CheckoutPage::get()->exists();
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', array(
			HtmlEditorField::create('PurchaseComplete', 'Purchase Complete', 4)
				->setDescription("This message is included in reciept email, after the customer submits the checkout")
		),'Metadata');
		return $fields;
	}
	
}

class CheckoutPage_Controller extends Page_Controller {

	private static $allowed_actions = array(
		'OrderForm',
		'payment',
		'PaymentForm'
	);
	
	/**
	 * Display a title if there is no model, or no title.
	 */
	public function Title() {
		if($this->Title)
			return $this->Title;
		return _t('CheckoutPage.TITLE',"Checkout");
	}

	function OrderForm() {
		if(!(bool)$this->Cart()){
			return false;
		}
		$class = self::config()->checkout_components;
		return new CheckoutForm(
			$this,
			'OrderForm',
			Injector::inst()->create("CheckoutComponentConfig", ShoppingCart::curr())
		);
	}

	/**
	 * Action for making on-site payments
	 */
	function payment(){
		return array(
			'Title' => 'Make Payment',
			'OrderForm' => $this->PaymentForm()
		);
	}

	function PaymentForm(){
		if(!(bool)$this->Cart()){
			return false;
		}
		$config = new CheckoutComponentConfig(ShoppingCart::curr(),false);
		$config->AddComponent(new OnsitePaymentCheckoutComponent());
		$form = new CheckoutForm($this, "PaymentForm", $config);
		$form->setActions(new FieldList(
			FormAction::create("submitpayment","Submit Payment")
		));

		return $form;
	}

}