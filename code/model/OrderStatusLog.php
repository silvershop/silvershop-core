<?php

/**
 * Data class that keeps a log of a single
 * status of an order.
 *
 * @package shop
 */
class OrderStatusLog extends DataObject
{
    private static $db                = array(
        'Title'          => 'Varchar(100)',
        'Note'           => 'Text',
        'DispatchedBy'   => 'Varchar(100)',
        'DispatchedOn'   => 'Date',
        'DispatchTicket' => 'Varchar(100)',
        'PaymentCode'    => 'Varchar(100)',
        'PaymentOK'      => 'Boolean',
        'SentToCustomer' => 'Boolean',
    );

    private static $has_one           = array(
        'Author' => 'Member',
        'Order'  => 'Order',
    );

    private static $searchable_fields = array(
        'Order.Reference' => array(
            'filter' => 'PartialMatchFilter',
            'title' => 'Order No'
        ),
        'Order.FirstName' => array(
            'filter' => 'PartialMatchFilter',
            'title' => 'First Name'
        ),
        'Order.Email' => array(
            'filter' => 'PartialMatchFilter',
            'title' => 'Email'
        )
    );

    private static $summary_fields = array(
        'Order.Reference' => 'Order No',
        'Created' => 'Created',
        'Order.Name' => 'Name',
        'Order.LatestEmail' => 'Email',
        'Title' => 'Title',
        'SentToCustomer' => 'Emailed'
    );

    private static $singular_name     = "Order Log Entry";

    private static $plural_name       = "Order Status Log Entries";

    private static $default_sort      = "\"Created\" DESC";


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
        if (!$this->AuthorID && $memberID = Member::currentUserID()) {
            $this->AuthorID = $memberID;
        }
        if (!$this->Title) {
            $this->Title = "Order Update";
        }
    }

    public function validate()
    {
        $validationResult = parent::validate();
        if (!$this->OrderID) {
            $validationResult->error('there is no order id for Order Status Log');
        }
        return $validationResult;
    }

    protected function updateWithLastInfo()
    {
        if ($this->OrderID) {
            if (
                $latestLog = OrderStatusLog::get()->filter('OrderID', $this->OrderID)->sort('Created', 'DESC')->first()
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
