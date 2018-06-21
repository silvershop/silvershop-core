<?php

namespace SilverShop\Reports;

use SilverShop\Model\Order;
use SilverShop\SQLQueryList\SQLQueryList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\i18n\i18nEntityProvider;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Reports\Report;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Base class for creating reports that can be filtered to a specific range.
 * Record grouping is also supported.
 */
abstract class ShopPeriodReport extends Report implements i18nEntityProvider
{
    private static $display_uncategorised_data = false;

    protected $dataClass = Order::class;

    protected $periodfield = '"SilverShop_Order"."Created"';

    protected $grouping = false;

    protected $pagesize = 30;

    private static $groupingdateformats = [
        'Year' => 'Y',
        'Month' => 'Y - F',
        'Day' => 'd F Y - l',
    ];

    public function title()
    {
        return _t(static::class . ".Title", $this->title);
    }

    public function description()
    {
        return _t(static::class . ".Description", $this->description);
    }

    public function parameterFields()
    {
        $member = Security::getCurrentUser() ? Security::getCurrentUser() : Member::create();
        $dateformat = $member->getDateFormat();
        $fields = FieldList::create(
            $start = DateField::create('StartPeriod', 'Start Date'),
            $end = DateField::create('EndPeriod', 'End Date')
        );
        if ($this->grouping) {
            $fields->push(
                DropdownField::create(
                    'Grouping',
                    'Group By',
                    [
                        'Year' => 'Year',
                        'Month' => 'Month',
                        'Day' => 'Day',
                    ],
                    'Month'
                )
            );
            if (self::config()->display_uncategorised_data) {
                $fields->push(
                    CheckboxField::create('IncludeUncategorised', 'Include Uncategorised Data')
                        ->setDescription('Display data that doesn\'t have a date.')
                );
            }
        }

        // When using silverware/calendar package, setting the date format breaks the admin interface.  Leave default
        // behavior as was, but allow the date format not to be set as a config override
        if ($this->config()->get('disable_set_date_format') != true) {
            $start->setDateFormat($dateformat);
            $end->setDateFormat($dateformat);
        }

        return $fields;
    }

    public function canView($member = null)
    {
        if (static::class === self::class) {
            return false;
        }
        return parent::canView($member);
    }

    public function getReportField()
    {
        $field = parent::getReportField();
        /**
         * @var GridFieldConfig $config
         */
        $config = $field->getConfig();
        if ($dataColumns = $config->getComponentByType(GridFieldDataColumns::class)) {
            $config->getComponentByType(GridFieldExportButton::class)
                ->setExportColumns($dataColumns->getDisplayFields($field));
        }

        return $field;
    }

    public function sourceRecords($params)
    {
        isset($params['Grouping']) || $params['Grouping'] = 'Month';
        $list = SQLQueryList::create($this->query($params));
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
        $start = $fields->fieldByName('StartPeriod')->dataValue();
        $end = $fields->fieldByName('EndPeriod')->dataValue();
        //include the entire end day
        if ($end) {
            $end = date('Y-m-d', strtotime($end) + 86400);
        }
        $filterperiod = $this->periodfield;
        $query = new ShopReportQuery();
        $query->setSelect(['FilterPeriod' => "MIN($filterperiod)"]);

        $table = DataObject::getSchema()->tableName($this->dataClass);

        $query->setFrom('"' . $table . '"');

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
                case 'Year':
                    $query->addGroupBy($this->fd($filterperiod, '%Y'));
                    break;
                case 'Month':
                default:
                    $query->addGroupBy($this->fd($filterperiod, '%Y') . ',' . $this->fd($filterperiod, '%m'));
                    break;
                case 'Day':
                    $query->addGroupBy(
                        $this->fd($filterperiod, '%Y') . ',' . $this->fd($filterperiod, '%m') . ',' . $this->fd(
                            $filterperiod,
                            '%d'
                        )
                    );
                    break;
            }
        }
        $query->setOrderBy('"FilterPeriod"', 'ASC');

        return $query;
    }

    protected function fd($date, $format)
    {
        return DB::get_conn()->formattedDatetimeClause($date, $format);
    }

    /**
     * Provide translatable entities for this class and all subclasses
     *
     * @return array
     */
    public function provideI18nEntities()
    {
        $cls = static::class;
        return [
            "{$cls}.Title" => [
                $this->title,
                "Title for the {$cls} report",
            ],
            "{$cls}.Description" => [
                $this->description,
                "Description for the {$cls} report",
            ],
        ];
    }
}
