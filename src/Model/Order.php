<?php

namespace SilverShop\Model;

use SilverShop\Cart\OrderTotalCalculator;
use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Extension\MemberExtension;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Modifiers\OrderModifier;
use SilverShop\ORM\Filters\MultiFieldPartialMatchFilter;
use SilverShop\ORM\OrderItemList;
use SilverShop\Page\AccountPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
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
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBEnum;
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
 *
 * @property DBCurrency $Total
 * @property string $Reference
 * @property DBDatetime $Placed
 * @property DBDatetime $Paid
 * @property DBDatetime $ReceiptSent
 * @property DBDatetime $Printed
 * @property DBDatetime $Dispatched
 * @property DBEnum $Status
 * @property string $FirstName
 * @property string $Surname
 * @property string $Email
 * @property string $Notes
 * @property string $IPAddress
 * @property bool $SeparateBillingAddress
 * @property string $Locale
 * @property int $MemberID
 * @property int $ShippingAddressID
 * @property int $BillingAddressID
 * @method   Member|MemberExtension Member()
 * @method   Address BillingAddress()
 * @method   Address ShippingAddress()
 * @method   OrderItem[]|HasManyList Items()
 * @method   OrderModifier[]|HasManyList Modifiers()
 * @method   OrderStatusLog[]|HasManyList OrderStatusLogs()
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
    private static $db = [
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

    private static $has_one = [
        'Member' => Member::class,
        'ShippingAddress' => Address::class,
        'BillingAddress' => Address::class,
    ];

    private static $has_many = [
        'Items' => OrderItem::class,
        'Modifiers' => OrderModifier::class,
        'OrderStatusLogs' => OrderStatusLog::class,
    ];

    private static $indexes = [
        'Status' => true,
        'StatusPlacedCreated' => [
            'type' => 'index',
            'columns' => ['Status', 'Placed', 'Created']
        ]
    ];

    private static $defaults = [
        'Status' => 'Cart',
    ];

    private static $casting = [
        'FullBillingAddress' => 'Text',
        'FullShippingAddress' => 'Text',
        'Total' => 'Currency',
        'SubTotal' => 'Currency',
        'TotalPaid' => 'Currency',
        'Shipping' => 'Currency',
        'TotalOutstanding' => 'Currency',
    ];

    private static $summary_fields = [
        'Reference',
        'Placed',
        'Name',
        'LatestEmail',
        'Total',
        'StatusI18N',
    ];

    private static $searchable_fields = [
        'Reference',
        'Name',
        'Email',
        'Status' => [
            'filter' => 'ExactMatchFilter',
            'field' => CheckboxSetField::class,
        ],
    ];

    private static $table_name = 'SilverShop_Order';

    private static $singular_name = 'Order';

    private static $plural_name = 'Orders';

    private static $default_sort = '"Placed" DESC, "Created" DESC';

    /**
     * Statuses for orders that have been placed.
     *
     * @config
     */
    private static $placed_status = [
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
     *
     * @config
     */
    private static $payable_status = [
        'Cart',
        'Unpaid',
        'Processing',
        'Sent',
    ];

    /**
     * Statuses that shouldn't show in user account.
     *
     * @config
     */
    private static $hidden_status = ['Cart'];


    /**
     * Statuses that should be logged in the Order-Status-Log
     *
     * @config
     * @var    array
     */
    private static $log_status = [];

    /**
     * Whether or not an order can be cancelled before payment
     *
     * @config
     * @var    bool
     */
    private static $cancel_before_payment = true;

    /**
     * Whether or not an order can be cancelled before processing
     *
     * @config
     * @var    bool
     */
    private static $cancel_before_processing = false;

    /**
     * Whether or not an order can be cancelled before sending
     *
     * @config
     * @var    bool
     */
    private static $cancel_before_sending = false;

    /**
     * Whether or not an order can be cancelled after sending
     *
     * @config
     * @var    bool
     */
    private static $cancel_after_sending = false;

    /**
     * Place an order before payment processing begins
     *
     * @config
     * @var    boolean
     */
    private static $place_before_payment = false;

    /**
     * Modifiers represent the additional charges or
     * deductions associated to an order, such as
     * shipping, taxes, vouchers etc.
     *
     * @config
     * @var    array
     */
    private static $modifiers = [];

    /**
     * Rounding precision of order amounts
     *
     * @config
     * @var    int
     */
    private static $rounding_precision = 2;

    /**
     * Minimal length (number of decimals) of order reference ids
     *
     * @config
     * @var    int
     */
    private static $reference_id_padding = 5;

    /**
     * Will allow completion of orders with GrandTotal=0,
     * which could be the case for orders paid with loyalty points or vouchers.
     * Will send the "Paid" date on the order, even though no actual payment was taken.
     * Will trigger the payment related extension points:
     * Order->onPayment, OrderItem->onPayment, Order->onPaid.
     *
     * @config
     * @var    boolean
     */
    private static $allow_zero_order_total = false;

    /**
     * A flag indicating that an order-status-log entry should be written
     *
     * @var bool
     */
    protected $flagOrderStatusWrite = false;

    public static function get_order_status_options()
    {
        $values = array();
        foreach (singleton(Order::class)->dbObject('Status')->enumValues(false) as $value) {
            $values[$value] = _t(__CLASS__ . '.STATUS_' . strtoupper($value), $value);
        }
        return $values;
    }

    /**
     * Create CMS fields for cms viewing and editing orders
     */
    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root', Tab::create('Main')));
        $fs = '<div class="field">';
        $fe = '</div>';
        $parts = array(
            DropdownField::create('Status', $this->fieldLabel('Status'), self::get_order_status_options()),
            LiteralField::create('Customer', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Customer') . $fe),
            LiteralField::create('Addresses', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Addresses') . $fe),
            LiteralField::create('Content', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Content') . $fe),
        );
        if ($this->Notes) {
            $parts[] = LiteralField::create('Notes', $fs . $this->renderWith('SilverShop\Admin\OrderAdmin_Notes') . $fe);
        }
        $fields->addFieldsToTab('Root.Main', $parts);

        $fields->addFieldToTab('Root.Modifiers', new GridField('Modifiers', 'Modifiers', $this->Modifiers()));

        $this->extend('updateCMSFields', $fields);

        if ($payments = $fields->fieldByName('Root.Payments.Payments')) {
            $fields->removeByName('Payments');
            $fields->insertAfter('Content', $payments);
            $payments->addExtraClass('order-payments');
        }

        return $fields;
    }

    /**
     * Augment field labels
     */
    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);

        $labels['Name'] = _t('SilverShop\Generic.Customer', 'Customer');
        $labels['LatestEmail'] = _t(__CLASS__ . '.db_Email', 'Email');
        $labels['StatusI18N'] = _t(__CLASS__ . '.db_Status', 'Status');

        return $labels;
    }

    /**
     * Adjust scafolded search context
     *
     * @return SearchContext the updated search context
     */
    public function getDefaultSearchContext()
    {
        $context = parent::getDefaultSearchContext();
        $fields = $context->getFields();

        $validStates = self::config()->placed_status;
        $statusOptions = array_filter(self::get_order_status_options(), function ($k) use ($validStates) {
            return in_array($k, $validStates);
        }, ARRAY_FILTER_USE_KEY);

        $fields->push(
            // TODO: Allow filtering by multiple statuses
            DropdownField::create('Status', $this->fieldLabel('Status'))
                ->setSource($statusOptions)
                ->setHasEmptyDefault(true)
        );

        // add date range filtering
        $fields->insertBefore(
            'Status',
            DateField::create('DateFrom', _t(__CLASS__ . '.DateFrom', 'Date from'))
        );

        $fields->insertBefore(
            'Status',
            DateField::create('DateTo', _t(__CLASS__ . '.DateTo', 'Date to'))
        );

        // get the array, to maniplulate name, and fullname seperately
        $filters = $context->getFilters();
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

        $context->setFilters($filters);

        $this->extend('updateDefaultSearchContext', $context);
        return $context;
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
    public function calculate()
    {
        $calculator = OrderTotalCalculator::create($this);
        return $this->Total = $calculator->calculate();
    }

    /**
     * This is needed to maintain backwards compatiability with
     * some subsystems using modifiers. eg discounts
     */
    public function getModifier($className, $forcecreate = false)
    {
        $calculator = OrderTotalCalculator::create($this);
        return $calculator->getModifier($className, $forcecreate);
    }

    /**
     * Enforce rounding precision when setting total
     */
    public function setTotal($val)
    {
        $this->setField('Total', round($val, self::$rounding_precision));
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
     * @return float
     */
    public function TotalOutstanding($includeAuthorized = true)
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
    public function getStatusI18N()
    {
        return _t(__CLASS__ . '.STATUS_' . strtoupper($this->Status), $this->Status);
    }

    /**
     * Get the link for finishing order processing.
     */
    public function Link()
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
     *
     * @return boolean
     */
    public function canCancel($member = null)
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
     *
     * @return boolean
     */
    public function canPay($member = null)
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
     *
     * @return boolean
     */
    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return false;
    }

    /**
     * Check if an order can be viewed.
     *
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return true;
    }

    /**
     * Check if an order can be edited.
     *
     * @return boolean
     */
    public function canEdit($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return true;
    }

    /**
     * Prevent standard creation of orders.
     *
     * @return boolean
     */
    public function canCreate($member = null, $context = array())
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
     *
     * @return string
     */
    public function Currency()
    {
        return ShopConfigExtension::get_site_currency();
    }

    /**
     * Get the latest email for this order.z
     */
    public function getLatestEmail()
    {
        if ($this->MemberID && ($this->Member()->LastEdited > $this->LastEdited || !$this->Email)) {
            return $this->Member()->Email;
        }
        return $this->getField('Email');
    }

    /**
     * Gets the name of the customer.
     */
    public function getName()
    {
        $firstname = $this->FirstName ? $this->FirstName : $this->Member()->FirstName;
        $surname = $this->FirstName ? $this->Surname : $this->Member()->Surname;
        return implode(' ', array_filter(array($firstname, $surname)));
    }

    public function getTitle()
    {
        return $this->Reference . ' - ' . $this->dbObject('Placed')->Nice();
    }

    /**
     * Get shipping address, or member default shipping address.
     */
    public function getShippingAddress()
    {
        return $this->getAddress('Shipping');
    }

    /**
     * Get billing address, if marked to use seperate address, otherwise use shipping address,
     * or the member default billing address.
     */
    public function getBillingAddress()
    {
        if (!$this->SeparateBillingAddress && $this->ShippingAddressID === $this->BillingAddressID) {
            return $this->getShippingAddress();
        } else {
            return $this->getAddress('Billing');
        }
    }

    /**
     * @param string $type - Billing or Shipping
     * @return Address
     * @throws \Exception
     */
    protected function getAddress($type)
    {
        $address = $this->getComponent($type . 'Address');

        if (!$address || !$address->exists() && $this->Member()) {
            $address = $this->Member()->{"Default${type}Address"}();
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
     *
     * @return boolean
     */
    public function getAddressesDiffer()
    {
        return $this->SeparateBillingAddress || $this->ShippingAddressID !== $this->BillingAddressID;
    }

    /**
     * Has this order been sent to the customer?
     * (at "Sent" status).
     *
     * @return boolean
     */
    public function IsSent()
    {
        return $this->Status == 'Sent';
    }

    /**
     * Is this order currently being processed?
     * (at "Sent" OR "Processing" status).
     *
     * @return boolean
     */
    public function IsProcessing()
    {
        return $this->IsSent() || $this->Status == 'Processing';
    }

    /**
     * Return whether this Order has been paid for (Status == Paid)
     * or Status == Processing, where it's been paid for, but is
     * currently in a processing state.
     *
     * @return boolean
     */
    public function IsPaid()
    {
        return (boolean)$this->Paid || $this->Status == 'Paid';
    }

    public function IsCart()
    {
        return $this->Status == 'Cart';
    }

    /**
     * Create a unique reference identifier string for this order.
     */
    public function generateReference()
    {
        $reference = str_pad($this->ID, self::$reference_id_padding, '0', STR_PAD_LEFT);

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
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->getField('Reference') && in_array($this->Status, self::$placed_status)) {
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
    protected function statusTransition($fromStatus, $toStatus)
    {
        // Add extension hook to react to order status transitions.
        $this->extend('onStatusChange', $fromStatus, $toStatus);

        if ($toStatus == 'Paid' && !$this->Paid) {
            $this->Paid = DBDatetime::now()->Rfc2822();
            foreach ($this->Items() as $item) {
                $item->onPayment();
            }
            //all payment is settled
            $this->extend('onPaid');

            if (!$this->ReceiptSent) {
                OrderEmailNotifier::create($this)->sendReceipt();
                $this->ReceiptSent = DBDatetime::now()->Rfc2822();
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
    protected function onBeforeDelete()
    {
        foreach ($this->Items() as $item) {
            $item->delete();
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

    public function onAfterWrite()
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

    public function debug()
    {
        if (Director::is_cli()) {
            // Temporarily disabled.
            // TODO: Reactivate when the following issue got fixed: https://github.com/silverstripe/silverstripe-framework/issues/7827
            return '';
        }

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
        $val .= "</div></div>";

        return $val;
    }

    /**
     * Provide i18n entities for the order class
     *
     * @return array
     */
    public function provideI18nEntities()
    {
        $entities = parent::provideI18nEntities();

        // collect all the payment status values
        foreach ($this->dbObject('Status')->enumValues() as $value) {
            $key = strtoupper($value);
            $entities[__CLASS__ . ".STATUS_$key"] = array(
                $value,
                "Translation of the order status '$value'",
            );
        }

        return $entities;
    }
}
