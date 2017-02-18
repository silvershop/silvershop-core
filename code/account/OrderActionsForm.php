<?php

use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\GatewayFieldsFactory;

/**
 * Perform actions on placed orders
 *
 * @package    shop
 * @subpackage forms
 */
class OrderActionsForm extends Form
{
    private static $allowed_actions    = array(
        'docancel',
        'dopayment',
        'httpsubmission',
    );

    private static $email_notification = false;

    private static $allow_paying       = true;

    private static $allow_cancelling   = true;

    private static $include_jquery = true;

    protected      $order;

    public function __construct($controller, $name, Order $order)
    {
        $this->order = $order;
        $fields = FieldList::create(
            HiddenField::create('OrderID', '', $order->ID)
        );
        $actions = FieldList::create();
        //payment
        if (self::config()->allow_paying && $order->canPay()) {
            $gateways = GatewayInfo::getSupportedGateways();
            //remove manual gateways
            foreach ($gateways as $gateway => $gatewayname) {
                if (GatewayInfo::isManual($gateway)) {
                    unset($gateways[$gateway]);
                }
            }
            if (!empty($gateways)) {
                $fields->push(
                    HeaderField::create(
                        "MakePaymentHeader",
                        _t("OrderActionsForm.MakePayment", "Make Payment")
                    )
                );
                $outstandingfield = Currency::create();
                $outstandingfield->setValue($order->TotalOutstanding(true));
                $fields->push(
                    LiteralField::create(
                        "Outstanding",
                        _t(
                            'Order.OutstandingWithAmount',
                            'Outstanding: {Amount}',
                            '',
                            array('Amount' => $outstandingfield->Nice())
                        )
                    )
                );
                $fields->push(
                    OptionsetField::create(
                        'PaymentMethod',
                        _t("OrderActionsForm.PaymentMethod", "Payment Method"),
                        $gateways,
                        key($gateways)
                    )
                );

                if ($ccFields = $this->getCCFields($gateways)) {
                    if ($this->config()->include_jquery) {
                       Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.min.js');
                    }
                    Requirements::javascript(SHOP_DIR . '/javascript/OrderActionsForm.js');
                    $fields->push($ccFields);
                }

                $actions->push(
                    FormAction::create(
                        'dopayment',
                        _t('OrderActionsForm.PayOrder', 'Pay outstanding balance')
                    )
                );
            }
        }
        //cancelling
        if (self::config()->allow_cancelling && $order->canCancel()) {
            $actions->push(
                FormAction::create(
                    'docancel',
                    _t('OrderActionsForm.CancelOrder', 'Cancel this order')
                )
            );
        }
        parent::__construct($controller, $name, $fields, $actions, OrderActionsForm_Validator::create(array(
            'PaymentMethod'
        )));
        $this->extend("updateForm", $order);
    }

    /**
     * Make payment for a place order, where payment had previously failed.
     *
     * @param array $data
     * @param Form  $form
     *
     * @return boolean
     */
    public function dopayment($data, $form)
    {
        if (self::config()->allow_paying
            && $this->order
            && $this->order->canPay()
        ) {
            // Save payment data from form and process payment
            $data = $form->getData();
            $gateway = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;

            if (!GatewayInfo::isManual($gateway)) {
                /** @var OrderProcessor $processor */
                $processor = OrderProcessor::create($this->order);
                $fieldFactory = new GatewayFieldsFactory(null);
                $response = $processor->makePayment(
                    $gateway,
                    $fieldFactory->normalizeFormData($data),
                    $processor->getReturnUrl()
                );
                if($response && !$response->isError()){
                    return $response->redirectOrRespond();
                } else {
                    $form->sessionMessage($processor->getError(), 'bad');
                }
            } else {
                $form->sessionMessage(_t('OrderActionsForm.ManualNotAllowed', "Manual payment not allowed"), 'bad');
            }

            return $this->controller->redirectBack();
        }
        $form->sessionMessage(
            _t('OrderForm.CouldNotProcessPayment', 'Payment could not be processed.'),
            'bad'
        );
        $this->controller->redirectBack();
    }

    /**
     * Form action handler for CancelOrderForm.
     *
     * Take the order that this was to be change on,
     * and set the status that was requested from
     * the form request data.
     *
     * @param array $data The form request data submitted
     * @param Form  $form The {@link Form} this was submitted on
     */
    public function docancel($data, $form)
    {
        if (self::config()->allow_cancelling
            && $this->order->canCancel()
        ) {
            $this->order->Status = 'MemberCancelled';
            $this->order->write();

            if (self::config()->email_notification) {
                OrderEmailNotifier::create($this->order)->sendCancelNotification();
            }

            $this->controller->sessionMessage(
                _t("OrderForm.OrderCancelled", "Order sucessfully cancelled"),
                'warning'
            );
            if (Member::currentUser() && $link = $this->order->Link()) {
                $this->controller->redirect($link);
            } else {
                $this->controller->redirectBack();
            }
        }
    }

    /**
     * Get credit card fields for the given gateways
     * @param array $gateways
     * @return CompositeField|null
     */
    protected function getCCFields(array $gateways)
    {
        $fieldFactory = new GatewayFieldsFactory(null, array('Card'));
        $onsiteGateways = array();
        $allRequired = array();
        foreach ($gateways as $gateway => $title) {
            if (!GatewayInfo::isOffsite($gateway)) {
                $required = GatewayInfo::requiredFields($gateway);
                $onsiteGateways[$gateway] = $fieldFactory->getFieldName($required);
                $allRequired += $required;
            }
        }

        $allRequired = array_unique($allRequired);
        $allRequired = $fieldFactory->getFieldName(array_combine($allRequired, $allRequired));

        if (empty($onsiteGateways)) {
            return null;
        }

        $ccFields = $fieldFactory->getCardFields();

        // Remove all the credit card fields that aren't required by any gateway
        foreach ($ccFields->dataFields() as $name => $field) {
            if ($name && !in_array($name, $allRequired)) {
                $ccFields->removeByName($name, true);
            }
        }

        $lookupField = LiteralField::create(
            '_CCLookupField',
            sprintf(
                '<span class="gateway-lookup" data-gateways=\'%s\'></span>',
                json_encode($onsiteGateways, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)
            )
        );

        $ccFields->push($lookupField);

        return CompositeField::create($ccFields)->setTag('fieldset')->addExtraClass('credit-card');
    }
}

class OrderActionsForm_Validator extends RequiredFields
{
    public function php($data)
    {
        // Check if we should do a payment
        if (Form::current_action() == 'dopayment' && !empty($data['PaymentMethod'])) {
            $gateway = $data['PaymentMethod'];
            // If the gateway isn't manual and not offsite, Check for credit-card fields!
            if (!GatewayInfo::isManual($gateway) && !GatewayInfo::isOffsite($gateway)) {
                $fieldFactory = new GatewayFieldsFactory(null);
                // Merge the required fields and the Credit-Card fields that are required for the gateway
                $this->required = $fieldFactory->getFieldName(array_merge($this->required, array_intersect(
                    array(
                        'type',
                        'name',
                        'number',
                        'startMonth',
                        'startYear',
                        'expiryMonth',
                        'expiryYear',
                        'cvv',
                        'issueNumber'
                    ),
                    GatewayInfo::requiredFields($gateway)
                )));
            }
        }

        return parent::php($data);
    }
}
