<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout;

use SilverShop\Checkout\CheckoutConfig;
use SilverShop\Checkout\Component\Membership;
use SilverShop\Checkout\ShopMemberFactory;
use SilverShop\Model\Order;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

/**
 * Creating an account during checkout: a missing email is caught before the checkout saves anything and reported on
 * the email field, and the errors ShopMemberFactory still raises are reported on their fields.
 */
class AccountCreationErrorsTest extends SapphireTest
{
    protected $usesDatabase = true;

    private const PASSWORD = 'Correct-Horse-42';

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Config::modify()
            ->set(CheckoutConfig::class, 'member_creation_enabled', true)
            ->set(CheckoutConfig::class, 'membership_required', false);
        $this->logOut();
        $this->order = Order::create();
        $this->order->write();
    }

    public function testAPasswordWithoutAnEmailIsReportedOnTheEmailFieldBeforeSaving(): void
    {
        $this->assertSame(
            [['Email', ValidationResult::TYPE_ERROR]],
            $this->errors(fn () => Membership::create()->validateData(
                $this->order,
                ['Email' => '', 'Password' => self::PASSWORD]
            ))
        );
    }

    public function testWithoutASubmittedEmailTheOrdersEmailIsUsed(): void
    {
        $this->order->Email = 'buyer@example.test';
        $this->order->write();
        $data = ['Password' => self::PASSWORD];

        $this->assertTrue(Membership::create()->validateData($this->order, $data));
        Membership::create()->setData($this->order, $data);

        $this->assertNotNull(Member::get()->find('Email', 'buyer@example.test'), 'account created with the order email');
    }

    public function testNoAccountIsNeededWithoutAPassword(): void
    {
        $this->assertTrue(Membership::create()->validateData($this->order, ['Email' => '', 'Password' => '']));
    }

    public function testTheFactoryReportsAMissingEmailOnTheEmailField(): void
    {
        $this->assertSame(
            [['Email', ValidationResult::TYPE_ERROR]],
            $this->errors(fn () => (new ShopMemberFactory())->create(['Password' => self::PASSWORD]))
        );
    }

    public function testTheFactoryReportsAMissingPasswordOnThePasswordField(): void
    {
        $this->assertSame(
            [['Password', ValidationResult::TYPE_ERROR]],
            $this->errors(fn () => (new ShopMemberFactory())->create(['Email' => 'new@example.test']))
        );
    }

    public function testTheFactoryReportsAnExistingAccountOnTheEmailField(): void
    {
        Member::create(['Email' => 'taken@example.test'])->write();

        $this->assertSame(
            [['Email', ValidationResult::TYPE_ERROR]],
            $this->errors(fn () => (new ShopMemberFactory())->create(
                ['Email' => 'taken@example.test', 'Password' => self::PASSWORD]
            ))
        );
    }

    public function testRefusedAccountCreationStaysAFormMessage(): void
    {
        Config::modify()->set(CheckoutConfig::class, 'member_creation_enabled', false);

        $this->assertSame(
            [['', ValidationResult::TYPE_ERROR]],
            $this->errors(fn () => (new ShopMemberFactory())->create(
                ['Email' => 'new@example.test', 'Password' => self::PASSWORD]
            ))
        );
    }

    /**
     * @return array<int, array{0: string, 1: string}> [fieldName, messageType] per error
     */
    private function errors(callable $call): array
    {
        try {
            $call();
        } catch (ValidationException $exception) {
            return array_values(array_map(
                static fn (array $message): array => [(string) $message['fieldName'], (string) $message['messageType']],
                $exception->getResult()->getMessages()
            ));
        }
        $this->fail('Expected a validation error');
    }
}
