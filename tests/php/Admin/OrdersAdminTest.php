<?php

declare(strict_types=1);

namespace SilverShop\Tests\Admin;

use SilverShop\Admin\OrdersAdmin;
use SilverShop\Model\Order;
use SilverShop\Tests\CapturesDeprecationNotices;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;

final class OrdersAdminTest extends SapphireTest
{
    use CapturesDeprecationNotices;

    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();
        $this->logInWithPermission('ADMIN');
    }

    public function testTotalColumnIsFormattedCurrency(): void
    {
        $order = $this->objFromFixture(Order::class, 'paid');

        $notices = $this->captureDeprecationNotices(function () use ($order, &$content) {
            $gridField = $this->getOrdersGridField();
            $content = $gridField->getConfig()
                ->getComponentByType(GridFieldDataColumns::class)
                ->getColumnContent($gridField, $order, 'Total');
        });

        $this->assertSame($order->dbObject('Total')->Nice(), $content);
        $this->assertEmpty($this->noticesFor($notices, Order::class . '->Total'));
    }

    public function testTotalColumnExportsRawValue(): void
    {
        $notices = $this->captureDeprecationNotices(function () use (&$csv) {
            $gridField = $this->getOrdersGridField();
            $csv = $gridField->getConfig()
                ->getComponentByType(GridFieldExportButton::class)
                ->generateExportFileData($gridField);
        });

        $rows = array_map(fn ($line) => str_getcsv($line, ',', '"', ''), array_filter(explode("\n", trim($csv))));
        $totalColumn = array_search('Total', $rows[0], true);
        $this->assertNotFalse($totalColumn, 'Export keeps the "Total" column header');

        $totals = array_column(array_slice($rows, 1), $totalColumn);
        $this->assertContains('408', $totals, 'Totals are exported as raw numbers, not formatted currency');
        $this->assertEmpty($this->noticesFor($notices, Order::class . '->Total'));
    }

    private function getOrdersGridField(): GridField
    {
        $request = new HTTPRequest('GET', 'admin/orders');
        $request->setSession(new Session([]));
        Injector::inst()->registerService($request, HTTPRequest::class);

        $admin = OrdersAdmin::create();
        $admin->setRequest($request);
        $admin->doInit();

        return $admin->getEditForm()->Fields()->fieldByName(str_replace('\\', '-', Order::class));
    }
}
