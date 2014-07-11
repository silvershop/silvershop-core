<?php

use Omnipay\Common\Helper;

/**
 *
 * This component should only ever be used on SSL encrypted pages!
 */
class OnsitePaymentCheckoutComponent extends CheckoutComponent {

    /** @var bool - automatically save cards if available in the gateway */
    private static $save_credit_cards = false;

    /** @var string - some might want this to be a fieldset? */
    private static $composite_field_tag = 'div';

    /** @var bool - set in getFormFields, used in validation methods */
    protected $hasExistingCards = false;


	public function getFormFields(Order $order) {
		$gateway = $this->getGateway($order);
		$gatewayfieldsfactory = new GatewayFieldsFactory($gateway, array('Card'));
		$fields = $gatewayfieldsfactory->getCardFields();
		if($gateway === "Dummy"){
			$fields->unshift(new LiteralField("dummypaymentmessage",
				"<p class=\"message good\">Dummy data has been added to the form for testing convenience.</p>"
			));
		}

        // add existing cards if present and allowed
        if (
            GatewayInfo::can_save_cards($gateway) &&
            Config::inst()->get('OnsitePaymentCheckoutComponent', 'save_credit_cards') &&
            $existingCardFields = $this->getExistingCardsFields()
        ) {
            Requirements::javascript('shop/javascript/CheckoutPage.js');

            // add the fields for a new address after the dropdown field
            $existingCardFields->merge($fields);

            // group under a composite field (invisible by default) so we
            // easily know which fields to show/hide
            $label = _t("OnsitePaymentCheckoutComponent.CreditCardContainer", "Payment Details");
            return new FieldList(
                CompositeField::create($existingCardFields)
                    ->addExtraClass('hasExistingValues')
                    ->setLegend($label)
                    ->setTag(Config::inst()->get('OnsitePaymentCheckoutComponent', 'composite_field_tag'))
            );
        }

		return $fields;
	}

	public function getRequiredFields(Order $order) {
		return GatewayInfo::required_fields( $this->getGateway($order) );
	}

    /**
     * Allow choosing from an existing credit cards
     * @return FieldList|null fields for
     */
    public function getExistingCardsFields() {
        $member = Member::currentUser();
        if($member && $member->SavedCreditCards()->exists()){
            $this->hasExistingCards = true;
            $cardOptions = $member->SavedCreditCards()->sort('Created', 'DESC')->map('ID', 'Name')->toArray();
            $cardOptions['newcard'] = _t('OnsitePaymentCheckoutComponent.CreateNewCard', 'Create a new card');
            $fieldtype = count($cardOptions) > 3 ? 'DropdownField' : 'OptionsetField';
            $label = _t("OnsitePaymentCheckoutComponent.ExistingCards", "Existing Credit Cards");
            return new FieldList(
                $fieldtype::create("SavedCreditCardID", $label,
                    $cardOptions,
                    $member->DefaultCreditCardID
                )->addExtraClass('existingValues')
            );
        }

        return null;
    }

    public function validateData(Order $order, array $data) {
		$result = new ValidationResult();
		//TODO: validate credit card data
		if(!Helper::validateLuhn($data['number'])){
			$result->error('Credit card is invalid');
			throw new ValidationException($result);
		}
	}

	public function getData(Order $order) {
		$data = array();
		$gateway = $this->getGateway($order);
		//provide valid dummy credit card data
		if($gateway === "Dummy"){
			$data = array_merge(array(
				'name' => 'Joe Bloggs',
				'number' => '4242424242424242',
				'cvv' => 123
			), $data);
		}
		return $data;
	}

	public function setData(Order $order, array $data) {
		//create payment?
	}

    /**
     * @param Order $order
     * @return string
     */
    protected function getGateway(Order $order) {
        return Checkout::get($order)->getSelectedPaymentMethod();
    }
}
