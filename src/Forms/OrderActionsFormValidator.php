<?php

namespace SilverShop\Forms;

use SilverStripe\Forms\RequiredFields;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;

class OrderActionsFormValidator extends RequiredFields
{
    public function php($data): bool
    {
        // Check if we should do a payment
        if (!empty($data['PaymentMethod'])) {
            $gateway = $data['PaymentMethod'];
            // If the gateway isn't manual and not offsite, Check for credit-card fields!
            if (!GatewayInfo::isManual($gateway) && !GatewayInfo::isOffsite($gateway)) {
                $fieldFactory = GatewayFieldsFactory::create(null);
                // Merge the required fields and the Credit-Card fields that are required for the gateway
                $this->required = $fieldFactory->getFieldName(
                    array_merge(
                        $this->required,
                        array_intersect(
                            [
                                'type',
                                'name',
                                'number',
                                'startMonth',
                                'startYear',
                                'expiryMonth',
                                'expiryYear',
                                'cvv',
                                'issueNumber'
                            ],
                            GatewayInfo::requiredFields($gateway)
                        )
                    )
                );
            }
        }

        return parent::php($data);
    }
}
