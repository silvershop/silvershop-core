<?php

namespace SilverShop\Tasks;

use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Model\Order;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;

/**
 * ShopEmailPreviewTask
 *
 * @author     Anselm Christophersen <ac@anselm.dk>
 * @date       September 2016
 * @package    shop
 * @subpackage tasks
 */

/**
 * ShopEmailPreviewTask
 */
class ShopEmailPreviewTask extends BuildTask
{
    protected $title = 'Preview Shop Emails';

    protected $description = 'Previews shop emails';

    private static $previewable_emails = [
        'Confirmation',
        'Receipt',
        'AdminNotification',
        'CancelNotification',
        'StatusChange'
    ];

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        $email = $request->remaining();
        $params = $request->allParams();
        $url = Director::absoluteURL("dev/{$params['Action']}/{$params['TaskName']}", true);
        $debug = true;

        if ($request->getVar('debug')) {
            $debug = $request->getVar('debug');
        }

        $previewableEmails = Config::inst()->get(self::class, 'previewable_emails') ?? [];

        echo '<h2>Choose Email</h2>';
        echo '<ul>';
        foreach ($previewableEmails as $key => $method) {
            echo '<li><a href="' . $url . '/' . $method . '">' . $method . '</a></li>';
        }
        echo '</ul><hr>';

        if ($email && in_array($email, $previewableEmails)) {
            $order = Order::get()->first();
            $notifier = OrderEmailNotifier::create($order);

            if ($debug) {
                $notifier->setDebugMode(true);
            }

            $method = "send$email";
            echo $notifier->$method();
        }
        //this is a little hardcore way of ending the party,
        //but as it's only used for styling, it works for now
        die;
    }
}
