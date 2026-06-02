<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Control\HTTPResponse;

class SummaryPaymentForm extends PaymentForm
{
    public function checkoutSubmit($data, $form): HTTPResponse
    {
        if ($this->controller && $this->controller->hasMethod('getFirstIncompleteCheckoutStepLink')) {
            $stepLink = $this->controller->getFirstIncompleteCheckoutStepLink();
            if ($stepLink) {
                return $this->controller->redirect($stepLink);
            }
        }

        return parent::checkoutSubmit($data, $form);
    }
}
