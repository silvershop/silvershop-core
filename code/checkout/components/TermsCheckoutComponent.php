<?php

class TermsCheckoutComponent extends CheckoutComponent {

	public function getFormFields(Order $order) {
		$fields = new FieldList();
		$page = SiteConfig::current_site_config()->TermsPage();

		if($page->exists()) {
			$fields->push(
				CheckboxField::create('ReadTermsAndConditions',
					sprintf(_t('CheckoutField.TERMSANDCONDITIONS',
						"I agree to the terms and conditions stated on the
							<a href=\"%s\" target=\"new\" class=\"read_terms\" title=\"Read the shop terms and conditions for this site\">
								terms and conditions
							</a>
						page"), $page->Link()
					)
				)->setCustomValidationMessage(
					_t("CheckoutField.MUSTAGREETOTERMS", "You must agree to the terms and conditions")
				)
			);
		}

		return $fields;
	}

	public function validateData(Order $order, array $data) {
		return true;
	}

	public function getData(Order $order) {
		return array();
	}

	public function setData(Order $order, array $data) {

	}

	public function getRequiredFields(Order $order) {
		$fields = parent::getRequiredFields($order);

		if(SiteConfig::current_site_config()->TermsPage()->exists()) {
			$fields[] = 'ReadTermsAndConditions';
		}

		return $fields;
	}
}
