<?php

namespace SilverShop\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Data class that keeps a log of a single
 * status of an order.
 *
 * @property string $Title
 * @property string $Note
 * @property string $DispatchedBy
 * @property DBDate $DispatchedOn
 * @property string $DispatchTicket
 * @property string $PaymentCode
 * @property bool $PaymentOK
 * @property bool $SentToCustomer Whether or not this entry has been sent to the customer (e.g. via OrderEmailNotifier)
 * @property bool $VisibleToCustomer Whether or not this entry should be visible to the customer (e.g. on order details)
 * @property int $AuthorID
 * @property int $OrderID
 *
 * @method Member Author()
 * @method Order Order()
 */
class OrderStatusLog extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(100)',
        'Note' => 'Text',
        'DispatchedBy' => 'Varchar(100)',
        'DispatchedOn' => 'Date',
        'DispatchTicket' => 'Varchar(100)',
        'PaymentCode' => 'Varchar(100)',
        'PaymentOK' => 'Boolean',
        'SentToCustomer' => 'Boolean',
        'VisibleToCustomer' => 'Boolean',
    ];

    private static $has_one = [
        'Author' => Member::class,
        'Order' => Order::class,
    ];

    private static $searchable_fields = [
        'Order.Reference' => [
            'filter' => 'PartialMatchFilter',
            'title' => 'Order No'
        ],
        'Order.FirstName' => [
            'filter' => 'PartialMatchFilter',
            'title' => 'First Name'
        ],
        'Order.Email' => [
            'filter' => 'PartialMatchFilter',
            'title' => 'Email'
        ]
    ];

    private static $summary_fields = [
        'Order.Reference' => 'Order No',
        'Created' => 'Created',
        'Order.Name' => 'Name',
        'Order.LatestEmail' => 'Email',
        'Title' => 'Title',
        'SentToCustomer' => 'Emailed',
        'VisibleToCustomer' => 'Visible to customer?'
    ];

    private static $singular_name = 'Order Log Entry';

    private static $plural_name = 'Order Status Log Entries';

    private static $default_sort = '"Created" DESC';

    private static $table_name = 'SilverShop_OrderStatusLog';

    /**
     * @var bool Whether the link between an Order and OrderStatusLog is required (tested during write validation)
     * @see static::validate()
     * @config
     */
    private static $order_is_required = true;

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return false;
    }

    public function populateDefaults()
    {
        parent::populateDefaults();
        $this->updateWithLastInfo();
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // If there are existing records with SentToCustomer=true and there are no records with VisibleToCustomer=true,
        // then we assume this is an upgrade (if a record was sent to the customer, then by definition it's visible to
        // the customer. However, we use the count check to ensure we only make the database change up until at least
        // one record has VisibleToCustomer = true (to avoid resetting it in future)
        if (OrderStatusLog::get()->filter('VisibleToCustomer', true)->count() == 0) {
            // We don't have any records with VisibleToCustomer true, so update all records with SentToCustomer = true
            $toUpdate = OrderStatusLog::get()->filter('SentToCustomer', true);
            $updated = 0;

            /** @var OrderStatusLog $log */
            foreach ($toUpdate as $log) {
                $log->VisibleToCustomer = true;
                $log->write();
                $updated++;
            }

            $message = sprintf(
                'Migrated %d records to new format (set VisibleToCustomer=true where SentToCustomer=true)',
                $updated
            );

            DB::alteration_message($message, 'changed');
        }
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->AuthorID && ($member = Security::getCurrentUser())) {
            $this->AuthorID = $member->ID;
        }
        if (!$this->Title) {
            $this->Title = 'Order Update';
        }
    }

    public function validate()
    {
        $validationResult = parent::validate();

        if (!$this->OrderID && $this->config()->order_is_required) {
            $validationResult->addError('there is no order id for Order Status Log');
        }

        return $validationResult;
    }

    protected function updateWithLastInfo()
    {
        if ($this->OrderID) {
            /** @var OrderStatusLog $latestLog */
            $latestLog = OrderStatusLog::get()
                ->filter('OrderID', $this->OrderID)
                ->sort('Created', 'DESC')
                ->first();

            if ($latestLog) {
                $this->DispatchedBy = $latestLog->DispatchedBy;
                $this->DispatchedOn = $latestLog->DispatchedOn;
                $this->DispatchTicket = $latestLog->DispatchTicket;
                $this->PaymentCode = $latestLog->PaymentCode;
                $this->PaymentOK = $latestLog->PaymentOK;
                $this->SentToCustomer = $latestLog->SentToCustomer;
                $this->VisibleToCustomer = $latestLog->VisibleToCustomer;
            }
        }
    }
}
