<?php

namespace SilverShop\Reports;

use SilverShop\Model\Order;
use SilverShop\SQLQueryList\SQLQueryList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\i18n\i18nEntityProvider;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Reports\Report;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Base class for creating reports that can be filtered to a specific range.
 * Record grouping is also supported.
 */
abstract class ShopPeriodReport extends Report implements i18nEntityProvider
{
    private static bool $display_uncategorised_data = false;

    protected $dataClass = Order::class;

    protected $periodfield = '"SilverShop_Order"."Created"';

    protected $grouping = false;

    protected $pagesize = 30;

    private static array $groupingdateformats = [
        'Year' => 'Y',
        'Month' => 'Y - F',
        'Day' => 'd F Y - l',
    ];

    public function title(): string
    {
        return _t(static::class . ".Title", $this->title);
    }

    public function description(): string
    {
        return _t(static::class . ".Description", $this->description);
    }

    public function parameterFields(): FieldList
    {
        $member = Security::getCurrentUser() ? Security::getCurrentUser() : Member::create();
        $dateformat = $member->getDateFormat();
        $fieldList = FieldList::create(
            $start = DateField::create('StartPeriod', 'Start Date'),
            $end = DateField::create('EndPeriod', 'End Date')
        );
        if ($this->grouping) {
            $fieldList->push(
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
                $fieldList->push(
                    CheckboxField::create('IncludeUncategorised', 'Include Uncategorised Data')
                        ->setDescription('Display data that doesn\'t have a date.')
                );
            }
        }

        // When using silverware/calendar package, setting the date format breaks the admin interface.  Leave default
        // behavior as was, but allow the date format not to be set as a config override
        if ($this->config()->get('disable_set_date_format') != true) {
            $start->setHTML5(false);
            $start->setDateFormat($dateformat);
            $end->setHTML5(false);
            $end->setDateFormat($dateformat);
        }

        return $fieldList;
    }

    public function canView($member = null): bool
    {
        if (static::class === self::class) {
            return false;
        }
        return parent::canView($member);
    }

    public function getReportField(): FormField
    {
        $formField = parent::getReportField();
        /**
         * @var GridFieldConfig $config
         */
        $config = $formField->getConfig();
        if ($dataColumns = $config->getComponentByType(GridFieldDataColumns::class)) {
            $config->getComponentByType(GridFieldExportButton::class)
                ->setExportColumns($dataColumns->getDisplayFields($formField));
        }

        return $formField;
    }

    public function sourceRecords($params): SQLQueryList
    {
        isset($params['Grouping']) || $params['Grouping'] = 'Month';
        $sqlQueryList = SQLQueryList::create($this->query($params));
        $grouping = $params['Grouping'];
        $self = $this;
        $sqlQueryList->setOutputClosure(
            function (array $row) use ($grouping, $self): object {
                $row['FilterPeriod'] = $self->formatDateForGrouping($row['FilterPeriod'], $grouping);

                return new $self->dataClass($row);
            }
        );

        return $sqlQueryList;
    }

    public function formatDateForGrouping($date, $grouping): string
    {
        if (!$date) {
            return $date;
        }
        $formats = self::config()->groupingdateformats;
        $dformat = $formats[$grouping];
        return date($dformat, strtotime($date));
    }

    public function query($params): ShopReportQuery|SQLSelect
    {
        //convert dates to correct format
        $fieldList = $this->parameterFields();
        $fieldList->setValues($params);
        $start = $fieldList->fieldByName('StartPeriod')->dataValue();
        $end = $fieldList->fieldByName('EndPeriod')->dataValue();
        //include the entire end day
        if ($end) {
            $end = date('Y-m-d', strtotime($end) + 86400);
        }
        $filterperiod = $this->periodfield;
        $shopReportQuery = new ShopReportQuery();
        $shopReportQuery->setSelect(['FilterPeriod' => "MIN($filterperiod)"]);

        $table = DataObject::getSchema()->tableName($this->dataClass);

        $shopReportQuery->setFrom('"' . $table . '"');

        if ($start && $end) {
            $shopReportQuery->addWhere("$filterperiod BETWEEN '$start' AND '$end'");
        } elseif ($start) {
            $shopReportQuery->addWhere("$filterperiod > '$start'");
        } elseif ($end) {
            $shopReportQuery->addWhere("$filterperiod <= '$end'");
        }
        if ($start || $end || !self::config()->display_uncategorised_data || !isset($params['IncludeUncategorised'])) {
            $shopReportQuery->addWhere("$filterperiod IS NOT NULL");
        }
        if ($this->grouping) {
            switch ($params['Grouping']) {
                case 'Year':
                    $shopReportQuery->addGroupBy($this->fd($filterperiod, '%Y'));
                    break;
                case 'Month':
                default:
                    $shopReportQuery->addGroupBy($this->fd($filterperiod, '%Y') . ',' . $this->fd($filterperiod, '%m'));
                    break;
                case 'Day':
                    $shopReportQuery->addGroupBy(
                        $this->fd($filterperiod, '%Y') . ',' . $this->fd($filterperiod, '%m') . ',' . $this->fd(
                            $filterperiod,
                            '%d'
                        )
                    );
                    break;
            }
        }
        $shopReportQuery->setOrderBy('"FilterPeriod"', 'ASC');

        return $shopReportQuery;
    }

    protected function fd($date, $format): string
    {
        return DB::get_conn()->formattedDatetimeClause($date, $format);
    }

    /**
     * Provide translatable entities for this class and all subclasses
     */
    public function provideI18nEntities(): array
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
