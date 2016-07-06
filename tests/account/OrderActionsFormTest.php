<?php

class OrderActionsFormTest extends FunctionalTest
{
    protected static $fixture_file = array(
        'silvershop/tests/fixtures/Pages.yml',
        'silvershop/tests/fixtures/shop.yml',
    );

    protected $order;
    protected $checkoutPage;

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();

        // create order from fixture and persist to DB
        $this->order = $this->objFromFixture("Order", "unpaid");
        $this->order->write();

        OrderManipulation::add_session_order($this->order);

        // create checkoug page from fixture and publish it
        $this->checkoutPage = $this->objFromFixture("CheckoutPage", "checkout");
        $this->checkoutPage->publish('Stage', 'Live');

        Config::inst()->update('Payment', 'allowed_gateways', array('Dummy'));
    }

    public function testOffsitePayment()
    {
        Config::inst()->update('GatewayInfo', 'Dummy', array('is_offsite' => true));
        $stubGateway = $this->buildPaymentGatewayStub(true, 'test-' . $this->order->ID, true);
        Injector::inst()->registerService($this->stubGatewayFactory($stubGateway), 'Omnipay\Common\GatewayFactory');

        $ctrl = ModelAsController::controller_for($this->checkoutPage);

        $response = Director::test($ctrl->Link('ActionsForm'), array(
            'action_dopayment' => true,
            'OrderID'       => $this->order->ID,
            'PaymentMethod' => 'Dummy'
        ), $this->session());

        // There should be a new payment
        $this->assertEquals(1, $this->order->Payments()->count());
        // The status of the payment should be pending purchase, as there's a redirect to the offsite gateway
        $this->assertEquals('PendingPurchase', $this->order->Payments()->first()->Status);
        // The response we get from submitting the form should be a redirect to the offsite payment form
        $this->assertEquals('http://paymentprovider/test/offsiteform', $response->getHeader('Location'));

    }

    public function testOnsitePayment()
    {
        $stubGateway = $this->buildPaymentGatewayStub(true, 'test-' . $this->order->ID, false);
        Injector::inst()->registerService($this->stubGatewayFactory($stubGateway), 'Omnipay\Common\GatewayFactory');

        $ctrl = ModelAsController::controller_for($this->checkoutPage);

        $response = Director::test($ctrl->Link('ActionsForm'), array(
            'action_dopayment' => true,
            'OrderID'       => $this->order->ID,
            'PaymentMethod' => 'Dummy',
            'type' => 'visa',
            'name' => 'Tester Mc. Testerson',
            'number' => '4242424242424242',
            'expiryMonth' => 10,
            'expiryYear' => date('Y') + 1,
            'cvv' => 123
        ), $this->session());

        // There should be a new payment
        $this->assertEquals(1, $this->order->Payments()->count());
        // The status of the payment should be Captured
        $this->assertEquals('Captured', $this->order->Payments()->first()->Status);
        // The response we get from submitting the form should be a redirect to the paid order
        $this->assertEquals($ctrl->Link('order/' . $this->order->ID), $response->getHeader('Location'));
    }

    public function testValidation()
    {
        $validator = new OrderActionsForm_Validator('PaymentMethod');
        $form = new OrderActionsForm(
            ModelAsController::controller_for($this->checkoutPage),
            'ActionsForm',
            $this->order
        );
        $validator->setForm($form);
        Form::set_current_action('dopayment');
        $validator->php(array(
            'OrderID'       => $this->order->ID,
            'PaymentMethod' => 'Dummy',
            'type' => 'visa',
            'name' => 'Tester Mc. Testerson',
            'number' => '4242424242424242'
        ));

        $requiredCount = 0;
        foreach ($validator->getErrors() as $error){
            if($error['messageType'] == 'required'){
                $requiredCount++;
            }
        }
        // 3 required fields missing
        $this->assertEquals(3, $requiredCount);
    }

    protected function stubGatewayFactory($stubGateway)
    {
        $factory = $this->getMockBuilder('Omnipay\Common\GatewayFactory')->getMock();
        $factory->expects($this->any())->method('create')->will($this->returnValue($stubGateway));
        return $factory;
    }

    protected function buildPaymentGatewayStub(
        $successValue,
        $transactionReference,
        $isRedirect = true
    ) {
        //--------------------------------------------------------------------------------------------------------------
        // request and response

        $mockResponse = $this->getMockBuilder('Omnipay\Common\Message\AbstractResponse')
            ->disableOriginalConstructor()->getMock();

        $mockResponse->expects($this->any())
            ->method('isSuccessful')->will($this->returnValue($successValue));

        $mockResponse->expects($this->any())
            ->method('isRedirect')->will($this->returnValue($isRedirect));

        $mockResponse->expects($this->any())
            ->method('getRedirectResponse')->will($this->returnValue(
                new \Symfony\Component\HttpFoundation\RedirectResponse('http://paymentprovider/test/offsiteform')
            ));

        $mockResponse->expects($this->any())
            ->method('getTransactionReference')->will($this->returnValue($transactionReference));

        $mockRequest = $this->getMockBuilder('Omnipay\Common\Message\AbstractRequest')
            ->disableOriginalConstructor()->getMock();

        $mockRequest->expects($this->any())
            ->method('send')->will($this->returnValue($mockResponse));

        $mockRequest->expects($this->any())
            ->method('getTransactionReference')->will($this->returnValue($transactionReference));


        //--------------------------------------------------------------------------------------------------------------
        // Build the gateway

        $stubGateway = $this->getMockBuilder('Omnipay\Common\AbstractGateway')
            ->setMethods(array('purchase', 'supportsCompletePurchase', 'getName'))
            ->getMock();

        $stubGateway->expects($this->any())
            ->method('purchase')
            ->will($this->returnValue($mockRequest));


        $stubGateway->expects($this->any())
            ->method('supportsCompletePurchase')
            ->will($this->returnValue($isRedirect));

        return $stubGateway;
    }
}
