<?php

namespace SilverShop\Reports;

use SilverShop\Page\Product;
use SilverShop\SQLQueryList\SQLQueryList;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;

class ProductReport extends ShopPeriodReport
{
    protected $title = 'Products';

    protected $description = 'Understand which products are performing, and which aren\'t.';

    protected $dataClass = Product::class;

    protected $periodfield = '"SilverShop_Order"."Created"';

    public function columns()
    {
        return [
            'Title' => [
                'title' => 'Title',
                'formatting' => '<a href=\"admin/catalog/Product/EditForm/field/Product/item/$ID/edit\" target=\"_new\">$Title</a>',
            ],
            'BasePrice' => 'Price',
            'Quantity' => 'Quantity',
            'Sales' => 'Sales',
        ];
    }


    public function sourceRecords($params)
    {
        $list = SQLQueryList::create($this->query($params));
        $self = $this;
        $list->setOutputClosure(
            function ($row) use ($self) {
                $row['BasePrice'] = $self->formatMoney($row['BasePrice']);
                $row['Sales'] = $self->formatMoney($row['Sales']);
                return new $self->dataClass($row);
            }
        );
        return $list;
    }

    private function formatMoney($money)
    {
        return number_format($money, 2);
    }

    public function query($params)
    {
        //convert dates to correct format
        $fields = $this->parameterFields();
        $fields->setValues($params);
        $start = $fields->fieldByName('StartPeriod')->dataValue();
        $end = $fields->fieldByName('EndPeriod')->dataValue();


        $table = DataObject::getSchema()->tableName($this->dataClass);
        $query = new SQLSelect();
        $query->setFrom('"' . $table . '"');

        $whereClue = '1';
        if ($start && $end) {
            $whereClue = sprintf(
                'DATE("o"."Placed") BETWEEN DATE(\'%s\') AND DATE(\'%s\')',
                $start,
                $end
            );
        } elseif ($start) {
            $whereClue = sprintf(
                'DATE("o"."Placed") > DATE(\'%s\')',
                $start
            );
        } elseif ($end) {
            $whereClue = sprintf(
                'DATE("o"."Placed") <= DATE(\'%s\')',
                $end
            );
        }

        $completedStatus = '\'' . implode('\', \'', [
                'Unpaid', 'Paid', 'Processing', 'Sent', 'Complete'
            ]) . '\'';


        $query->setSelect(
            [
                '"SiteTree"."ID"',
                '"SiteTree"."Title"',
                '"SilverShop_Product"."BasePrice"',
            ]
        )
            ->selectField(
                sprintf(
                    '(
                        SELECT
                            SUM(soi."Quantity")
                        FROM
                            "SilverShop_Product_OrderItem" spo,
                            "SilverShop_OrderItem" soi,
                            "SilverShop_OrderAttribute" soa,
                            "SilverShop_Order" o
                        WHERE
                            spo.ProductID = "SilverShop_Product"."ID"
                            AND spo.ID = soi.ID
                            AND soi.ID = spo.ID
                            AND spo.ID = soa.ID
                            AND soa.OrderID = o.ID
                            AND o.Status IN (%s)
                            AND %s
                    )',
                    $completedStatus,
                    $whereClue
                ),
                'Quantity'
            )
            ->selectField(
                sprintf(
                    '(
                        SELECT
                            SUM(soa."CalculatedTotal")
                        FROM
                            "SilverShop_Product_OrderItem" spo,
                            "SilverShop_OrderItem" soi,
                            "SilverShop_OrderAttribute" soa,
                            "SilverShop_Order" o
                        WHERE
                            spo.ProductID = "SilverShop_Product"."ID"
                            AND spo.ID = soi.ID
                            AND soi.ID = spo.ID
                            AND spo.ID = soa.ID
                            AND soa.OrderID = o.ID
                            AND o.Status IN (%s)
                            AND %s
                    )',
                    $completedStatus,
                    $whereClue
                ),
                'Sales'
            )
        ;

        $query->addInnerJoin('SiteTree', '"SilverShop_Product"."ID" = "SiteTree"."ID"');
        $query->setOrderBy('Quantity DESC');
        return $query;
    }
}
