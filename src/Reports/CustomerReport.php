<?php

namespace SilverShop\Reports;

use SilverStripe\Security\Member;

/**
 * List top customers, especially those who spend alot, and those who buy alot.
 *
 * @todo customer making the most purchases
 * @todo customer who has spent the most money
 * @todo new registrations graph
 * @todo demographics
 */
class CustomerReport extends ShopPeriodReport
{
    protected $title = 'Customers';
    protected $description = 'Understand which customers spend the most.';

    protected $dataClass = Member::class;

    protected $periodfield = '"SilverShop_Order"."Paid"';

    public function columns()
    {
        return [
            'FirstName' => 'First Name',
            'Surname' => 'Surname',
            'Email' => 'Email',
            'Created' => 'Joined',
            'Spent' => 'Spent',
            'Orders' => 'Orders',
            'edit' => [
                'title' => 'Edit',
                'formatting' => '<a href=\"admin/security/EditForm/field/Members/item/$ID/edit\" target=\"_new\">edit</a>',
            ],
        ];
    }

    public function getReportField()
    {
        $field = parent::getReportField();
        return $field;
    }

    public function query($params)
    {
        $query = parent::query($params);
        $query->selectField($this->periodfield, 'FilterPeriod')
            ->addSelect(
                ['"Member"."ID"', '"Member"."FirstName"', '"Member"."Surname"', '"Member"."Email"', '"Member"."Created"']
            )
            ->selectField('COUNT("SilverShop_Order"."ID")', 'Orders')
            ->selectField('SUM("SilverShop_Order"."Total")', 'Spent');

        $query->addInnerJoin('SilverShop_Order', '"Member"."ID" = "SilverShop_Order"."MemberID"');

        $query->addGroupBy('"Member"."ID"');

        if (!$query->getOrderBy()) {
            $query->setOrderBy(
                [
                'Spent' => 'DESC',
                'Orders' => 'DESC'
                ]
            );
        }
        $query->setLimit(50);
        return $query;
    }
}
