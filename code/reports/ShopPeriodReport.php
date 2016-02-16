<?php

/**
 * Base class for creating reports that can be filtered to a specific range.
 * Record grouping is also supported.
 */
class ShopPeriodReport extends SS_Report
{
    private static $display_uncategorised_data = false;

    protected      $dataClass                  = 'Order';

    protected      $periodfield                = "\"Order\".\"Created\"";

    protected      $grouping                   = false;

    protected      $pagesize                   = 30;

    private static $groupingdateformats        = array(
        "Year"  => "Y",
        "Month" => "Y - F",
        "Day"   => "d F Y - l",
    );

    public function title()
    {
        return _t($this->class . ".TITLE", $this->title);
    }

    public function description()
    {
        return _t($this->class . ".DESCRIPTION", $this->description);
    }

    public function parameterFields()
    {
        $member = Member::currentUserID() ? Member::currentUser() : Member::create();
        $dateformat = $member->getDateFormat();
        $fields = FieldList::create(
            $start = DateField::create("StartPeriod", "Start Date"),
            $end = DateField::create("EndPeriod", "End Date")
        );
        if ($this->grouping) {
            $fields->push(
                DropdownField::create(
                    "Grouping",
                    "Group By",
                    array(
                        "Year"  => "Year",
                        "Month" => "Month",
                        "Day"   => "Day",
                    ),
                    'Month'
                )
            );
            if (self::config()->display_uncategorised_data) {
                $fields->push(
                    CheckboxField::create("IncludeUncategorised", "Include Uncategorised Data")
                        ->setDescription("Display data that doesn't have a date.")
                );
            }
        }
        $start->setConfig("dateformat", $dateformat);
        $end->setConfig("dateformat", $dateformat);
        $start->setConfig("showcalendar", true);
        $end->setConfig("showcalendar", true);
        return $fields;
    }

    public function canView($member = null)
    {
        if (get_class($this) == "ShopPeriodReport") {
            return false;
        }
        return parent::canView($member);
    }

    public function getReportField()
    {
        $field = parent::getReportField();
        $config = $field->getConfig();
        $columns = $config->getComponentByType("GridFieldDataColumns")
            ->getDisplayFields($field);
        $config->getComponentByType('GridFieldExportButton')
            ->setExportColumns($columns);
        return $field;
    }

    public function sourceRecords($params)
    {
        isset($params['Grouping']) || $params['Grouping'] = "Month";
        $list = new SQLQueryList($this->query($params));
        $grouping = $params['Grouping'];
        $self = $this;
        $list->setOutputClosure(
            function ($row) use ($grouping, $self) {
                $row['FilterPeriod'] = $self->formatDateForGrouping($row['FilterPeriod'], $grouping);

                return new $self->dataClass($row);
            }
        );

        return $list;
    }

    public function formatDateForGrouping($date, $grouping)
    {
        if (!$date) {
            return $date;
        }
        $formats = self::config()->groupingdateformats;
        $dformat = $formats[$grouping];
        return date($dformat, strtotime($date));
    }

    public function query($params)
    {
        //convert dates to correct format
        $fields = $this->parameterFields();
        $fields->setValues($params);
        $start = $fields->fieldByName("StartPeriod")->dataValue();
        $end = $fields->fieldByName("EndPeriod")->dataValue();
        //include the entire end day
        if ($end) {
            $end = date('Y-m-d', strtotime($end) + 86400);
        }
        $filterperiod = $this->periodfield;
        $query = new ShopReport_Query();
        $query->setSelect(array("FilterPeriod" => "MIN($filterperiod)"));

        $query->setFrom('"' . $this->dataClass . '"');

        if ($start && $end) {
            $query->addWhere("$filterperiod BETWEEN '$start' AND '$end'");
        } elseif ($start) {
            $query->addWhere("$filterperiod > '$start'");
        } elseif ($end) {
            $query->addWhere("$filterperiod <= '$end'");
        }
        if ($start || $end || !self::config()->display_uncategorised_data || !isset($params['IncludeUncategorised'])) {
            $query->addWhere("$filterperiod IS NOT NULL");
        }
        if ($this->grouping) {
            switch ($params['Grouping']) {
                case "Year":
                    $query->addGroupBy($this->fd($filterperiod, '%Y'));
                    break;
                case "Month":
                default:
                    $query->addGroupBy($this->fd($filterperiod, '%Y') . "," . $this->fd($filterperiod, '%m'));
                    break;
                case "Day":
                    $query->addGroupBy(
                        $this->fd($filterperiod, '%Y') . "," . $this->fd($filterperiod, '%m') . "," . $this->fd(
                            $filterperiod,
                            '%d'
                        )
                    );
                    break;
            }
        }
        $query->setOrderBy("\"FilterPeriod\"", "ASC");

        return $query;
    }

    protected function fd($date, $format)
    {
        return DB::getConn()->formattedDatetimeClause($date, $format);
    }
}

class ShopReport_Query extends SQLQuery
{
    public function canSortBy($fieldName)
    {
        return true;
    }
}
