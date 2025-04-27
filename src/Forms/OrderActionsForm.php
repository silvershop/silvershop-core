<?php

namespace SilverShop\Forms;

use SilverStripe\Control\RequestHandler;
use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Omnipay\Exception\InvalidConfigurationException;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Perform actions on placed orders
 */
class OrderActionsForm extends Form
{
    private static array $allowed_actions = [
        'docancel',
        'dopayment',
        'httpsubmission',
    ];

    private static bool $email_notification = false;

    private static bool $allow_paying = true;

    private static bool $allow_cancelling = true;

    private static bool $include_jquery = true;

    protected Order $order;

    /**
     * OrderActionsForm constructor.
     *
     * @param  $controller
     * @param  $name
     * @throws InvalidConfigurationException
     */
    public function __construct(RequestHandler $requestHandler, $name, Order $order)
    {
        $this->order = $order;
        $fieldList = FieldList::create(
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
                $fieldList->push(
                    HeaderField::create(
                        'MakePaymentHeader',
                        _t(__CLASS__ . '.MakePayment', 'Make Payment')
                    )
                );
                $outstandingfield = DBCurrency::create_field(DBCurrency::class, $order->TotalOutstanding(true));
                $fieldList->push(
                    LiteralField::create(
                        'Outstanding',
                        _t(
                            'SilverShop\Model\Order.OutstandingWithAmount',
                            'Outstanding: {Amount}',
                            '',
                            ['Amount' => $outstandingfield->Nice()]
                        )
                    )
                );
                $fieldList->push(
                    OptionsetField::create(
                        'PaymentMethod',
                        _t(__CLASS__ . '.PaymentMethod', 'Payment Method'),
                        $gateways,
                        key($gateways)
                    )
                );

                if (($ccFields = $this->getCCFields($gateways)) instanceof CompositeField) {
                    if ($this->config()->include_jquery) {
                        Requirements::javascript('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js');
                    }
                    Requirements::javascript('silvershop/core: client/dist/javascript/OrderActionsForm.js');
                    $fieldList->push($ccFields);
                }

                $actions->push(
                    FormAction::create(
                        'dopayment',
                        _t(__CLASS__ . '.PayOrder', 'Pay outstanding balance')
                    )->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
                );
            }
        }
        //cancelling
        if (self::config()->allow_cancelling && $order->canCancel()) {
            $actions->push(
                FormAction::create(
                    'docancel',
                    _t(__CLASS__ . '.CancelOrder', 'Cancel this order')
                )->setValidationExempt(true)
            );
        }

        parent::__construct(
            $requestHandler,
            $name,
            $fieldList,
            $actions,
            OrderActionsFormValidator::create(
                [
                    'PaymentMethod'
                ]
            )
        );
        $this->extend('updateForm', $order);
    }

    /**
     * Make payment for a place order, where payment had previously failed.
     *
     * @param array $data
     * @param Form  $form
     */
    public function dopayment($data, $form): HTTPResponse
    {
        if (self::config()->allow_paying
            && $this->order
            && $this->order->canPay()
        ) {
            // Save payment data from form and process payment
            $data = $form->getData();
            $gateway = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;

            if (!GatewayInfo::isManual($gateway)) {
                $processor = OrderProcessor::create($this->order);
                $fieldFactory = GatewayFieldsFactory::create(null);
                $response = $processor->makePayment(
                    $gateway,
                    $fieldFactory->normalizeFormData($data),
                    $processor->getReturnUrl()
                );
                if ($response && !$response->isError()) {
                    return $response->redirectOrRespond();
                }
                $form->sessionMessage($processor->getError(), 'bad');
            } else {
                $form->sessionMessage(_t(__CLASS__ . '.ManualNotAllowed', 'Manual payment not allowed'), 'bad');
            }

            return $this->controller->redirectBack();
        }
        $form->sessionMessage(
            _t(__CLASS__ . '.CouldNotProcessPayment', 'Payment could not be processed.'),
            'bad'
        );
        return $this->controller->redirectBack();
    }

    /**
     * Form action handler for CancelOrderForm.
     *
     * Take the order that this was to be change on,
     * and set the status that was requested from
     * the form request data.
     *
     * @param  array $data The form request data submitted
     * @param  Form  $form The {@link Form} this was submitted on
     * @throws ValidationException
     */
    public function docancel($data, $form): void
    {
        if (self::config()->allow_cancelling
            && $this->order->canCancel()
        ) {
            $this->order->setField('Status', 'MemberCancelled');
            $this->order->write();

            if (self::config()->email_notification) {
                OrderEmailNotifier::create($this->order)->sendCancelNotification();
            }

            $this->controller->sessionMessage(
                _t(__CLASS__ . '.OrderCancelled', 'Order sucessfully cancelled'),
                'warning'
            );
            if (Security::getCurrentUser() && $link = $this->order->Link()) {
                $this->controller->redirect($link);
            } else {
                $this->controller->redirectBack();
            }
        }
    }

    /**
     * Get credit card fields for the given gateways
     */
    protected function getCCFields(array $gateways): ?CompositeField
    {
        $gatewayFieldsFactory = GatewayFieldsFactory::create(null, ['Card']);
        $onsiteGateways = [];
        $allRequired = [];
        foreach ($gateways as $gateway => $title) {
            if (!GatewayInfo::isOffsite($gateway)) {
                $required = GatewayInfo::requiredFields($gateway);
                $onsiteGateways[$gateway] = $gatewayFieldsFactory->getFieldName($required);
                $allRequired += $required;
            }
        }

        $allRequired = array_unique($allRequired);
        $allRequired = $gatewayFieldsFactory->getFieldName(array_combine($allRequired, $allRequired));

        if ($onsiteGateways === []) {
            return null;
        }

        $fieldList = $gatewayFieldsFactory->getCardFields();

        // Remove all the credit card fields that aren't required by any gateway
        foreach ($fieldList->dataFields() as $name => $formField) {
            if ($name && !in_array($name, $allRequired)) {
                $fieldList->removeByName($name, true);
            }
        }

        $literalField = LiteralField::create(
            '_CCLookupField',
            sprintf(
                '<span class="gateway-lookup" data-gateways=\'%s\'></span>',
                json_encode($onsiteGateways, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)
            )
        );

        $fieldList->push($literalField);

        return CompositeField::create($fieldList)->setTag('fieldset')->addExtraClass('credit-card');
    }
}
