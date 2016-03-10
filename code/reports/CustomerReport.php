<?php

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
    protected $title       = "Customers";

    protected $dataClass   = "Member";

    protected $periodfield = "Order.Paid";

    public function columns()
    {
        return array(
            "FirstName" => "First Name",
            "Surname"   => "Surname",
            "Email"     => "Email",
            "Created"   => "Joined",
            "Spent"     => "Spent",
            "Orders"    => "Orders",
            "NumVisit"  => "Visits",
            "edit"      => array(
                "title"      => "Edit",
                "formatting" => '<a href=\"admin/security/EditForm/field/Members/item/$ID/edit\" target=\"_new\">edit</a>',
            ),

        );
    }

    public function getReportField()
    {
        $field = parent::getReportField();
        return $field;
    }

    public function query($params)
    {
        $query = parent::query($params);
        $query->selectField($this->periodfield, "FilterPeriod")
            ->addSelect(
                array("Member.ID", "Member.FirstName", "Member.Surname", "Member.Email", "NumVisit", "Member.Created")
            )
            ->selectField("Count(Order.ID)", "Orders")
            ->selectField("Sum(Order.Total)", "Spent");
        $query->addInnerJoin("Order", "Member.ID = Order.MemberID");
        $query->addGroupBy("Member.ID");
        if (!$query->getOrderBy()) {
            $query->setOrderBy("Spent DESC,Orders DESC");
        }
        $query->setLimit("50");
        return $query;
    }
}
