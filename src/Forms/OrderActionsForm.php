<?php

namespace SilverShop\Forms;

use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Perform actions on placed orders
 */
class OrderActionsForm extends Form
{
    private static $allowed_actions = [
        'docancel',
        'dopayment',
        'httpsubmission',
    ];

    private static $email_notification = false;

    private static $allow_paying = true;

    private static $allow_cancelling = true;

    private static $include_jquery = true;

    /**
     * @var Order the order
     */
    protected $order;

    /**
     * OrderActionsForm constructor.
     *
     * @param  $controller
     * @param  $name
     * @param  Order      $order
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
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
                        'MakePaymentHeader',
                        _t(__CLASS__ . '.MakePayment', 'Make Payment')
                    )
                );
                $outstandingfield = DBCurrency::create_field(DBCurrency::class, $order->TotalOutstanding(true));
                $fields->push(
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
                $fields->push(
                    OptionsetField::create(
                        'PaymentMethod',
                        _t(__CLASS__ . '.PaymentMethod', 'Payment Method'),
                        $gateways,
                        key($gateways)
                    )
                );

                if ($ccFields = $this->getCCFields($gateways)) {
                    if ($this->config()->include_jquery) {
                        Requirements::javascript('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js');
                    }
                    Requirements::javascript('silvershop/core: client/dist/javascript/OrderActionsForm.js');
                    $fields->push($ccFields);
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
            $controller,
            $name,
            $fields,
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
     *
     * @return HTTPResponse
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
                $processor = OrderProcessor::create($this->order);
                $fieldFactory = new GatewayFieldsFactory(null);
                $response = $processor->makePayment(
                    $gateway,
                    $fieldFactory->normalizeFormData($data),
                    $processor->getReturnUrl()
                );
                if ($response && !$response->isError()) {
                    return $response->redirectOrRespond();
                } else {
                    $form->sessionMessage($processor->getError(), 'bad');
                }
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
     * @throws \SilverStripe\ORM\ValidationException
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
     *
     * @param  array $gateways
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
