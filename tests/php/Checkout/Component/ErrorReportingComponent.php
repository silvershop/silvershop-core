<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout\Component;

use SilverShop\Checkout\Component\CheckoutComponent;
use SilverShop\Model\Order;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

/**
 * A checkout component (think of a delivery date picker or a VAT number field) that rejects the submitted data
 * with the errors it was given.
 */
class ErrorReportingComponent extends CheckoutComponent implements TestOnly
{
    /**
     * @param array<int, array{message: string, field?: string, code?: string}> $errors
     */
    public function __construct(protected array $errors = [])
    {
    }

    public function getFormFields(Order $order): FieldList
    {
        return FieldList::create(
            TextField::create('DeliveryDate', 'Delivery date'),
            TextField::create('VatNumber', 'VAT number')
        );
    }

    public function validateData(Order $order, array $data): bool
    {
        if ($this->errors === []) {
            return true;
        }

        $validationResult = ValidationResult::create();
        foreach ($this->errors as $error) {
            $validationResult->addFieldError(
                $error['field'] ?? '',
                $error['message'],
                ValidationResult::TYPE_ERROR,
                $error['code'] ?? ''
            );
        }

        throw ValidationException::create($validationResult);
    }

    public function getData(Order $order): array
    {
        return [];
    }

    public function setData(Order $order, array $data): Order
    {
        return $order;
    }
}
