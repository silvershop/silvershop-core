<?php

class ProductReport extends ShopPeriodReport
{
    protected $title       = "Products";

    protected $description = "Understand which products are performing, and which aren't.";

    protected $dataClass   = "Product";

    protected $periodfield = "SiteTree.Created";

    public function columns()
    {
        return array(
            "Title"     => array(
                "title"      => "Title",
                "formatting" => '<a href=\"admin/catalog/Product/EditForm/field/Product/item/$ID/edit\" target=\"_new\">$Title</a>',
            ),
            "BasePrice" => "Price",
            "Created"   => "Created",
            "Quantity"  => "Quantity",
            "Sales"     => "Sales",
        );
    }

    public function query($params)
    {
        $query = parent::query($params);
        $query->selectField($this->periodfield, "FilterPeriod")
            ->addSelect(
                array(
                    "Product.ID",
                    "SiteTree.ClassName",
                    "SiteTree.Title",
                    "Product.BasePrice",
                    "SiteTree.Created",
                )
            )
            ->selectField("Count(OrderItem.Quantity)", "Quantity")
            ->selectField("Sum(OrderAttribute.CalculatedTotal)", "Sales");
        $query->addInnerJoin("SiteTree", "Product.ID = SiteTree.ID");
        $query->addLeftJoin("Product_OrderItem", "Product.ID = Product_OrderItem.ProductID");
        $query->addLeftJoin("OrderItem", "Product_OrderItem.ID = OrderItem.ID");
        $query->addLeftJoin("OrderAttribute", "Product_OrderItem.ID = OrderAttribute.ID");
        $query->addLeftJoin("Order", "OrderAttribute.OrderID = Order.ID");
        $query->addGroupby("Product.ID");
        $query->addWhere("\"Order\".\"Paid\" IS NOT NULL OR \"Product_OrderItem\".\"ID\" IS NULL");

        return $query;
    }
}
