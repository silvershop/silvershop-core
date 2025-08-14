<?php

namespace SilverShop\Tasks;

use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Model\Order;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
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

    protected $previewableEmails = [
        'Confirmation',
        'Receipt',
        'AdminNotification',
        'CancelNotification',
        'StatusChange'
    ];

    /**
     * @param HTTPRequest $request
     */
    public function run($request): void
    {
        $email = $request->remaining();
        $params = $request->allParams();
        $url = Director::absoluteURL("dev/{$params['Action']}/{$params['TaskName']}");
        $debug = true;

        if ($request->getVar('debug')) {
            $debug = $request->getVar('debug');
        }

        echo '<h2>Choose Email</h2>';
        echo '<ul>';
        foreach ($this->previewableEmails as $method) {
            echo '<li><a href="' . $url . '/' . $method . '">' . $method . '</a></li>';
        }
        echo '</ul><hr>';

        if ($email && in_array($email, $this->previewableEmails)) {
            $order = Order::get()->first();
            $notifier = OrderEmailNotifier::create($order);

            if ($debug) {
                $notifier->setDebugMode(true);
            }

            $method = "send$email";

            if ($email == 'StatusChange') {
                echo $notifier->$method('This is a test title', 'This is a test note');
            } else {
                echo $notifier->$method();
            }
        }
        //this is a little hardcore way of ending the party,
        //but as it's only used for styling, it works for now
        die;
    }
}
