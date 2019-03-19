<?php

namespace SilverShop\Model;

use SilverStripe\ORM\DataObject;
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
 * @property bool $SentToCustomer
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
        'SentToCustomer' => 'Emailed'
    ];

    private static $singular_name = 'Order Log Entry';

    private static $plural_name = 'Order Status Log Entries';

    private static $default_sort = '"Created" DESC';

    private static $table_name = 'SilverShop_OrderStatusLog';

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
        if (!$this->OrderID) {
            $validationResult->addError('there is no order id for Order Status Log');
        }
        return $validationResult;
    }

    protected function updateWithLastInfo()
    {
        if ($this->OrderID) {
            if ($latestLog = OrderStatusLog::get()->filter('OrderID', $this->OrderID)->sort('Created', 'DESC')->first()
            ) {
                $this->DispatchedBy = $latestLog->DispatchedBy;
                $this->DispatchedOn = $latestLog->DispatchedOn;
                $this->DispatchTicket = $latestLog->DispatchTicket;
                $this->PaymentCode = $latestLog->PaymentCode;
                $this->PaymentOK = $latestLog->PaymentOK;
            }
        }
    }
}
