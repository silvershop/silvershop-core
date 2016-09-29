<?php

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
        'Receipt'
    );


    /**
     * @param SS_HTTPRequest $request
     */
    public function run($request)
    {
        $email = $request->remaining();
        $params = $request->allParams();
        $url = "/dev/{$params['Action']}/{$params['TaskName']}";
        if ($email && in_array($email,$this->previewableEmails)) {
            echo '<a href="' . $url . '">back</a><hr>';
            $order = Order::get()->first();
            $notifier = OrderEmailNotifier::create($order)
                ->setDebugMode(true);
            $method = "send$email";
            echo $notifier->$method();

        } else {
            echo '<h2>Choose Email</h2>';
            echo '<ul>';
            foreach ($this->previewableEmails as $key => $method) {
                echo '<li><a href="' . $url . '/' . $method . '">' . $method . '</a></li>';
            }
            echo '</ul><hr>';
        }
        //this is a little hardcore way of ending the party,
        //but as it's only used for styling, it works for now
        die;
    }
}
