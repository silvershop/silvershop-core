<?php

namespace SilverShop\Core\Tasks;


use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;



/**
 * ShopEmailPreviewTask
 *
 * @author Anselm Christophersen <ac@anselm.dk>
 * @date   September 2016
 * @package    shop
 * @subpackage tasks
 */

/**
 * ShopEmailPreviewTask
 *
 */
class ShopEmailPreviewTask extends BuildTask
{
    protected $title       = "Preview Shop Emails";

    protected $description = 'Previews shop emails';

    protected $previewableEmails = array(
        'Confirmation',
        'Receipt',
        'AdminNotification'
    );


    /**
     * @param SS_HTTPRequest $request
     */
    public function run($request)
    {
        $email = $request->remaining();
        $params = $request->allParams();
        $url = Director::absoluteURL("dev/{$params['Action']}/{$params['TaskName']}", true);

        echo '<h2>Choose Email</h2>';
        echo '<ul>';
        foreach ($this->previewableEmails as $key => $method) {
            echo '<li><a href="' . $url . '/' . $method . '">' . $method . '</a></li>';
        }
        echo '</ul><hr>';

        if ($email && in_array($email,$this->previewableEmails)) {
            $order = Order::get()->first();
            $notifier = OrderEmailNotifier::create($order)
                ->setDebugMode(true);
            $method = "send$email";
            echo $notifier->$method();

        } else {

        }
        //this is a little hardcore way of ending the party,
        //but as it's only used for styling, it works for now
        die;
    }
}
