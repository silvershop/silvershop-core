<?php

namespace SilverShop\Model;

use Exception;
use SilverShop\Cart\OrderTotalCalculator;
use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Modifiers\OrderModifier;
use SilverShop\ORM\Filters\MultiFieldPartialMatchFilter;
use SilverShop\ORM\OrderItemList;
use SilverShop\Page\AccountPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Omnipay\Extensions\Payable;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\Filters\GreaterThanFilter;
use SilverStripe\ORM\Filters\LessThanFilter;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * The order class is a databound object for handling Orders
 * within SilverStripe.
 *
 * @mixin Payable
 * @property float $Total
 * @property mixed $Reference
 * @property ?string $Placed
 * @property ?string $Paid
 * @property ?string $ReceiptSent
 * @property ?string $Printed
 * @property ?string $Dispatched
 * @property ?string $Status
 * @property ?string $FirstName
 * @property ?string $Surname
 * @property ?string $Email
 * @property ?string $Notes
 * @property ?string $IPAddress
 * @property bool $SeparateBillingAddress
 * @property ?string $Locale
 * @property int $MemberID
 * @property int $ShippingAddressID
 * @property int $BillingAddressID
 * @method Member Member()
 * @method Address BillingAddress()
 * @method Address ShippingAddress()
 * @method HasManyList<OrderItem> Items()
 * @method HasManyList<OrderModifier> Modifiers()
 * @method HasManyList<OrderStatusLog> OrderStatusLogs()
 */
class Order extends DataObject
{
    /**
     * Status codes and what they mean:
     *
     * Unpaid (default): Order created but no successful payment by customer yet
     * Query: Order not being processed yet (customer has a query, or could be out of stock)
     * Paid: Order successfully paid for by customer
     * Processing: Order paid for, package is currently being processed before shipping to customer
     * Sent: Order paid for, processed for shipping, and now sent to the customer
     * Complete: Order completed (paid and shipped). Customer assumed to have received their goods
     * AdminCancelled: Order cancelled by the administrator
     * MemberCancelled: Order cancelled by the customer (Member)
     */
    private static array $db = [
        'Total' => 'Currency',
        'Reference' => 'Varchar', //allow for customised order numbering schemes
        //status
        'Placed' => 'Datetime', //date the order was placed (went from Cart to Order)
        'Paid' => 'Datetime', //no outstanding payment left
        'ReceiptSent' => 'Datetime', //receipt emailed to customer
        'Printed' => 'Datetime',
        'Dispatched' => 'Datetime', //products have been sent to customer
        'Status' => "Enum('Unpaid,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled,Cart','Cart')",
        //customer (for guest orders)
        'FirstName' => 'Varchar',
        'Surname' => 'Varchar',
        'Email' => 'Varchar',
        'Notes' => 'Text',
        'IPAddress' => 'Varchar(15)',
        //separate shipping
        'SeparateBillingAddress' => 'Boolean',
        // keep track of customer locale
        'Locale' => 'Locale',
    ];

    private static array $has_one = [
        'Member' => Member::class,
        'ShippingAddress' => Address::class,
        'BillingAddress' => Address::class,
    ];

    private static array $has_many = [
        'Items' => OrderItem::class,
        'Modifiers' => OrderModifier::class,
        'OrderStatusLogs' => OrderStatusLog::class,
    ];

    private static array $indexes = [
        'Status' => true,
        'StatusPlacedCreated' => [
            'type' => 'index',
            'columns' => ['Status', 'Placed', 'Created']
        ]
    ];

    private static array $defaults = [
        'Status' => 'Cart',
    ];

    private static array $casting = [
        'FullBillingAddress' => 'Text',
        'FullShippingAddress' => 'Text',
        'Total' => 'Currency',
        'SubTotal' => 'Currency',
        'TotalPaid' => 'Currency',
        'Shipping' => 'Currency',
        'TotalOutstanding' => 'Currency',
    ];

    private static array $summary_fields = [
        'Reference',
        'Placed',
        'Name',
        'LatestEmail',
        'Total',
        'StatusI18N',
    ];

    private static array $searchable_fields = [
        'Reference',
        'Name',
        'Email',
        'Status' => [
            'filter' => 'ExactMatchFilter',
            'field' => CheckboxSetField::class,
        ],
    ];

    private static string $table_name = 'SilverShop_Order';

    private static string $singular_name = 'Order';

    private static string $plural_name = 'Orders';

    private static string $default_sort = '"Placed" DESC, "Created" DESC';

    /**
     * Statuses for orders that have been placed.
     */
    private static array $placed_status = [
        'Paid',
        'Unpaid',
        'Processing',
        'Sent',
        'Complete',
        'MemberCancelled',
        'AdminCancelled',
    ];

    /**
     * Statuses for which an order can be paid for
     */
    private static array $payable_status = [
        'Cart',
        'Unpaid',
        'Processing',
        'Sent',
    ];

    /**
     * Statuses that shouldn't show in user account.
     */
    private static array $hidden_status = ['Cart'];


    /**
     * Statuses that should be logged in the Order-Status-Log
     */
    private static array $log_status = [];

    /**
     * Whether or not an order can be cancelled before payment
     */
    private static bool $cancel_before_payment = true;

    /**
     * Email customer an invoice upon payment
     */
    private static bool $send_receipt = true;

    /**
     * Whether or not an order can be cancelled before processing
     */
    private static bool $cancel_before_processing = false;

    /**
     * Whether or not an order can be cancelled before sending
     */
    private static bool $cancel_before_sending = false;

    /**
     * Whether or not an order can be cancelled after sending
     */
    private static bool $cancel_after_sending = false;

    /**
     * Place an order before payment processing begins
     */
    private static bool $place_before_payment = false;

    /**
     * Modifiers represent the additional charges or
     * deductions associated to an order, such as
     * shipping, taxes, vouchers etc.
     */
    private static array $modifiers = [];

    /**
     * Rounding precision of order amounts
     */
    private static int $rounding_precision = 2;

    /**
     * Minimal length (number of decimals) of order reference ids
     */
    private static int $reference_id_padding = 5;

    /**
     * Will allow completion of orders with GrandTotal=0,
     * which could be the case for orders paid with loyalty points or vouchers.
     * Will send the "Paid" date on the order, even though no actual payment was taken.
     * Will trigger the payment related extension points:
     * Order->onPayment, OrderItem->onPayment, Order->onPaid.
     */
    private static bool $allow_zero_order_total = false;

    /**
     * A flag indicating that an order-status-log entry should be written
     */
    protected bool $flagOrderStatusWrite = false;

    /**
     * @return mixed[]
     */
    public static function get_order_status_options(): array
    {
        $values = [];
        foreach (singleton(Order::class)->dbObject('Status')->enumValues(false) as $value) {
            $values[$value] = _t(__CLASS__ . '.STATUS_' . strtoupper($value), $value);
        }
        return $values;
    }

    /**
     * Create CMS fields for cms viewing and editing orders
     */
    public function getCMSFields(): FieldList
    {
        $fieldList = FieldList::create(TabSet::create('Root', Tab::create('Main')));
        $fs = '<div class="field">';
        $fe = '</div>';
        $parts = [
            DropdownField::create('Status', $this->fieldLabel('Status'), self::get_order_status_options()),
            LiteralField::create('Customer', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Customer') . $fe),
            LiteralField::create('Addresses', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Addresses') . $fe),
            LiteralField::create('Content', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Content') . $fe),
        ];
        if ($this->Notes) {
            $parts[] = LiteralField::create('Notes', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Notes') . $fe);
        }
        $fieldList->addFieldsToTab('Root.Main', $parts);

        $fieldList->addFieldToTab('Root.Modifiers', GridField::create('Modifiers', 'Modifiers', $this->Modifiers()));

        $this->extend('updateCMSFields', $fieldList);

        if ($payments = $fieldList->fieldByName('Root.Payments.Payments')) {
            $fieldList->removeByName('Payments');
            $fieldList->insertAfter('Content', $payments);
            $payments->addExtraClass('order-payments');
        }

        return $fieldList;
    }

    /**
     * Augment field labels
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);

        $labels['Name'] = _t('SilverShop\Generic.Customer', 'Customer');
        $labels['LatestEmail'] = _t(__CLASS__ . '.db_Email', 'Email');
        $labels['StatusI18N'] = _t(__CLASS__ . '.db_Status', 'Status');

        return $labels;
    }

    /**
     * Adjust scafolded search context
     * returns the updated search context
     */
    public function getDefaultSearchContext(): SearchContext
    {
        $searchContext = parent::getDefaultSearchContext();
        $fieldList = $searchContext->getFields();

        $validStates = self::config()->placed_status;
        $statusOptions = array_filter(self::get_order_status_options(), function ($k) use ($validStates): bool {
            return in_array($k, $validStates);
        }, ARRAY_FILTER_USE_KEY);

        $fieldList->push(
            // TODO: Allow filtering by multiple statuses
            DropdownField::create('Status', $this->fieldLabel('Status'))
                ->setSource($statusOptions)
                ->setHasEmptyDefault(true)
        );

        // add date range filtering
        $fieldList->insertBefore(
            'Status',
            DateField::create('DateFrom', _t(__CLASS__ . '.DateFrom', 'Date from'))
        );

        $fieldList->insertBefore(
            'Status',
            DateField::create('DateTo', _t(__CLASS__ . '.DateTo', 'Date to'))
        );

        // get the array, to maniplulate name, and fullname seperately
        $filters = $searchContext->getFilters();
        $filters['DateFrom'] = GreaterThanFilter::create('Placed');
        $filters['DateTo'] = LessThanFilter::create('Placed');

        // filter customer need to use a bunch of different sources
        $filters['Name'] = MultiFieldPartialMatchFilter::create(
            'FirstName',
            false,
            ['SplitWords'],
            [
                'Surname',
                'Member.FirstName',
                'Member.Surname',
                'BillingAddress.FirstName',
                'BillingAddress.Surname',
                'ShippingAddress.FirstName',
                'ShippingAddress.Surname',
            ]
        );

        $searchContext->setFilters($filters);

        $this->extend('updateDefaultSearchContext', $searchContext);
        return $searchContext;
    }

    /**
     * Hack for swapping out relation list with OrderItemList
     *
     * @inheritdoc
     */
    public function getComponents($componentName, $id = null)
    {
        $components = parent::getComponents($componentName, $id);
        if ($componentName === 'Items' && get_class($components) !== UnsavedRelationList::class) {
            $query = $components->dataQuery();
            $components = OrderItemList::create(OrderItem::class, 'OrderID');
            $components->setDataQuery($query);
            $components = $components->forForeignID($this->ID);
        }
        return $components;
    }

    /**
     * Returns the subtotal of the items for this order.
     */
    public function SubTotal()
    {
        if ($this->Items()->exists()) {
            return $this->Items()->SubTotal();
        }

        return 0;
    }

    /**
     * Calculate the total
     *
     * @return float the final total
     */
    public function calculate(): float
    {
        $orderTotalCalculator = OrderTotalCalculator::create($this);
        return $this->Total = $orderTotalCalculator->calculate();
    }

    /**
     * This is needed to maintain backwards compatiability with
     * some subsystems using modifiers. eg discounts
     */
    public function getModifier($className, $forcecreate = false)
    {
        $orderTotalCalculator = OrderTotalCalculator::create($this);
        return $orderTotalCalculator->getModifier($className, $forcecreate);
    }

    /**
     * Enforce rounding precision when setting total
     */
    public function setTotal($val): void
    {
        $this->setField('Total', round($val, static::config()->get('rounding_precision')));
    }

    /**
     * Get final value of order.
     * Retrieves value from DataObject's record array.
     */
    public function Total()
    {
        return $this->getField('Total');
    }

    /**
     * Alias for Total.
     */
    public function GrandTotal()
    {
        return $this->Total();
    }

    /**
     * Calculate how much is left to be paid on the order.
     * Enforces rounding precision.
     *
     * Payments that have been authorized via a non-manual gateway should count towards the total paid amount.
     * However, it's possible to exclude these by setting the $includeAuthorized parameter to false, which is
     * useful to determine the status of the Order. Order status should only change to 'Paid' when all
     * payments are 'Captured'.
     *
     * @param  bool $includeAuthorized whether or not to include authorized payments (excluding manual payments)
     */
    public function TotalOutstanding($includeAuthorized = true): float
    {
        return round(
            $this->GrandTotal() - ($includeAuthorized ? $this->TotalPaidOrAuthorized() : $this->TotalPaid()),
            self::config()->rounding_precision
        );
    }

    /**
     * Get the order status. This will return a localized value if available.
     *
     * @return string the payment status
     */
    public function getStatusI18N(): string
    {
        return _t(__CLASS__ . '.STATUS_' . strtoupper($this->Status), $this->Status);
    }

    /**
     * Get the link for finishing order processing.
     */
    public function Link(): string
    {
        $link = CheckoutPage::find_link(false, 'order', $this->ID);

        if (Security::getCurrentUser()) {
            $link = Controller::join_links(AccountPage::find_link(), 'order', $this->ID);
        }

        $this->extend('updateLink', $link);

        return $link;
    }

    /**
     * Returns TRUE if the order can be cancelled
     * PRECONDITION: Order is in the DB.
     */
    public function canCancel($member = null): bool
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        switch ($this->Status) {
            case 'Unpaid' :
            return self::config()->cancel_before_payment;
            case 'Paid' :
            return self::config()->cancel_before_processing;
            case 'Processing' :
            return self::config()->cancel_before_sending;
            case 'Sent' :
            case 'Complete' :
            return self::config()->cancel_after_sending;
        }
        return false;
    }

    /**
     * Check if an order can be paid for.
     */
    public function canPay($member = null): bool
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        if (!in_array($this->Status, self::config()->payable_status)) {
            return false;
        }
        if ($this->TotalOutstanding(true) > 0 && empty($this->Paid)) {
            return true;
        }
        return false;
    }

    /**
     * Prevent deleting orders.
     */
    public function canDelete($member = null): bool
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return false;
    }

    /**
     * Check if an order can be viewed.
     */
    public function canView($member = null): bool
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return true;
    }

    /**
     * Check if an order can be edited.
     */
    public function canEdit($member = null): bool
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return true;
    }

    /**
     * Prevent standard creation of orders.
     */
    public function canCreate($member = null, $context = []): bool
    {
        $extended = $this->extendedCan(__FUNCTION__, $member, $context);
        if ($extended !== null) {
            return $extended;
        }

        return false;
    }

    /**
     * Return the currency of this order.
     * Note: this is a fixed value across the entire site.
     */
    public function Currency(): string
    {
        return ShopConfigExtension::get_site_currency();
    }

    /**
     * Get the latest email for this order.z
     */
    public function getLatestEmail()
    {
        if ($this->hasMethod('overrideLatestEmail')) {
            return $this->overrideLatestEmail();
        }
        if ($this->MemberID && ($this->Member()->LastEdited > $this->LastEdited || !$this->Email)) {
            return $this->Member()->Email;
        }
        return $this->getField('Email');
    }

    /**
     * Gets the name of the customer.
     */
    public function getName(): string
    {
        $firstname = $this->FirstName ? $this->FirstName : $this->Member()->FirstName;
        $surname = $this->FirstName ? $this->Surname : $this->Member()->Surname;
        return implode(' ', array_filter([$firstname, $surname]));
    }

    public function getTitle()
    {
        return $this->Reference . ' - ' . $this->dbObject('Placed')->Nice();
    }

    /**
     * Get shipping address, or member default shipping address.
     */
    public function getShippingAddress(): ?Address
    {
        return $this->getAddress('Shipping');
    }

    /**
     * Get billing address, if marked to use seperate address, otherwise use shipping address,
     * or the member default billing address.
     */
    public function getBillingAddress(): ?Address
    {
        if (!$this->SeparateBillingAddress && $this->ShippingAddressID === $this->BillingAddressID) {
            return $this->getShippingAddress();
        }
        return $this->getAddress('Billing');
    }

    /**
     * @param string $type - Billing or Shipping
     * @throws Exception
     */
    protected function getAddress(string $type): ?Address
    {
        $address = $this->getComponent($type . 'Address');

        if (!$address || !$address->exists() && $this->Member()) {
            $address = $this->Member()->{"Default{$type}Address"}();
        }

        if (empty($address->Surname) && empty($address->FirstName)) {
            if ($member = $this->Member()) {
                // If there's a member object, use information from the Member.
                // The information from Order should have precendence if set though!
                $address->FirstName = $this->FirstName ?: $member->FirstName;
                $address->Surname = $this->Surname ?: $member->Surname;
            } else {
                $address->FirstName = $this->FirstName;
                $address->Surname = $this->Surname;
            }
        }

        return $address;
    }

    /**
     * Check if the two addresses saved differ.
     */
    public function getAddressesDiffer(): bool
    {
        return $this->SeparateBillingAddress || $this->ShippingAddressID !== $this->BillingAddressID;
    }

    /**
     * Has this order been sent to the customer?
     * (at "Sent" status).
     */
    public function IsSent(): bool
    {
        return $this->Status == 'Sent';
    }

    /**
     * Is this order currently being processed?
     * (at "Sent" OR "Processing" status).
     */
    public function IsProcessing(): bool
    {
        if ($this->IsSent()) {
            return true;
        }
        return $this->Status == 'Processing';
    }

    /**
     * Return whether this Order has been paid for (Status == Paid)
     * or Status == Processing, where it's been paid for, but is
     * currently in a processing state.
     */
    public function IsPaid(): bool
    {
        return (boolean)$this->Paid || $this->Status == 'Paid';
    }

    public function IsCart(): bool
    {
        return $this->Status == 'Cart';
    }

    /**
     * Create a unique reference identifier string for this order.
     */
    public function generateReference(): void
    {
        $reference = str_pad($this->ID, static::config()->get('reference_id_padding'), '0', STR_PAD_LEFT);

        $this->extend('generateReference', $reference);

        $candidate = $reference;
        //prevent generating references that are the same
        $count = 0;
        while (Order::get()->filter('Reference', $candidate)->count() > 0) {
            $count++;
            $candidate = $reference . '' . $count;
        }
        $this->Reference = $candidate;
    }

    /**
     * Get the reference for this order, or fall back to order ID.
     */
    public function getReference()
    {
        return $this->getField('Reference') ? $this->getField('Reference') : $this->ID;
    }

    /**
     * Force creating an order reference
     */
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        if (!$this->getField('Reference') && in_array($this->Status, static::config()->get('placed_status'))) {
            $this->generateReference();
        }

        // perform status transition
        if ($this->isInDB() && $this->isChanged('Status')) {
            $this->statusTransition(
                empty($this->original['Status']) ? 'Cart' : $this->original['Status'],
                $this->Status
            );
        }

        // While the order is unfinished/cart, always store the current locale with the order.
        // We do this everytime an order is saved, because the user might change locale (language-switch).
        if ($this->Status == 'Cart') {
            $this->Locale = ShopTools::get_current_locale();
        }
    }

    /**
     * Called from @see onBeforeWrite whenever status changes
     *
     * @param string $fromStatus status to transition away from
     * @param string $toStatus   target status
     */
    protected function statusTransition($fromStatus, $toStatus): void
    {
        // Add extension hook to react to order status transitions.
        $this->extend('onStatusChange', $fromStatus, $toStatus);

        if ($toStatus == 'Paid' && !$this->Paid) {
            $this->setField('Paid', DBDatetime::now()->Rfc2822());
            foreach ($this->Items() as $hasManyList) {
                $hasManyList->onPayment();
            }
            //all payment is settled
            $this->extend('onPaid');

            if (!$this->ReceiptSent && static::config()->get('send_receipt')) {
                OrderEmailNotifier::create($this)->sendReceipt();
                $this->setField('ReceiptSent', DBDatetime::now()->Rfc2822());
            }
        }

        $logStatus = $this->config()->log_status;
        if (!empty($logStatus) && in_array($toStatus, $logStatus)) {
            $this->flagOrderStatusWrite = $fromStatus != $toStatus;
        }
    }

    /**
     * delete attributes, statuslogs, and payments
     */
    protected function onBeforeDelete(): void
    {
        foreach ($this->Items() as $hasManyList) {
            $hasManyList->delete();
        }

        foreach ($this->Modifiers() as $modifier) {
            $modifier->delete();
        }

        foreach ($this->OrderStatusLogs() as $logEntry) {
            $logEntry->delete();
        }

        // just remove the payment relations…
        // that way payment objects still persist (they might be relevant for book-keeping?)
        $this->Payments()->removeAll();

        parent::onBeforeDelete();
    }

    public function onAfterWrite(): void
    {
        parent::onAfterWrite();

        //create an OrderStatusLog
        if ($this->flagOrderStatusWrite) {
            $this->flagOrderStatusWrite = false;
            $log = OrderStatusLog::create();

            // populate OrderStatusLog
            $log->Title = _t(
                'SilverShop\ShopEmail.StatusChanged',
                'Status for order #{OrderNo} changed to "{OrderStatus}"',
                '',
                ['OrderNo' => $this->Reference, 'OrderStatus' => $this->getStatusI18N()]
            );
            $log->Note = _t('SilverShop\ShopEmail.StatusChange' . $this->Status . 'Note', $this->Status . 'Note');
            $log->OrderID = $this->ID;
            OrderEmailNotifier::create($this)->sendStatusChange($log->Title, $log->Note);
            $log->SentToCustomer = true; // Explicitly set because sendStatusChange() won't set it in this case
            $log->VisibleToCustomer = true;
            $this->extend('updateOrderStatusLog', $log);
            $log->write();
        }
    }

    public function debug(): string
    {
        $val = "<div class='order'><h1>" . static::class . "</h1>\n<ul>\n";
        if ($this->record) {
            foreach ($this->record as $fieldName => $fieldVal) {
                $val .= "\t<li>$fieldName: " . Debug::text($fieldVal) . "</li>\n";
            }
        }
        $val .= "</ul>\n";
        $val .= "<div class='items'><h2>Items</h2>";
        if ($items = $this->Items()) {
            $val .= $this->Items()->debug();
        }
        $val .= "</div><div class='modifiers'><h2>Modifiers</h2>";
        if ($modifiers = $this->Modifiers()) {
            $val .= $modifiers->debug();
        }

        return $val . "</div></div>";
    }

    /**
     * Provide i18n entities for the order class
     */
    public function provideI18nEntities(): array
    {
        $entities = parent::provideI18nEntities();

        // collect all the payment status values
        foreach ($this->dbObject('Status')->enumValues() as $value) {
            $key = strtoupper($value);
            $entities[__CLASS__ . ".STATUS_$key"] = [
                $value,
                "Translation of the order status '$value'",
            ];
        }

        return $entities;
    }
}
