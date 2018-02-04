<?php

namespace SilverShop\Extension;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Forms\OrderActionsForm;
use SilverShop\Model\Order;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Security;

/**
 * Provides forms and processing to a controller for editing an
 * order that has been previously placed.
 *
 * @property Controller $owner
 */
class OrderManipulationExtension extends Extension
{
    private static $allowed_actions = array(
        'ActionsForm',
        'order',
    );

    private static $sessname = 'OrderManipulation.historicalorders';

    /**
     * Add an order to the session-stored history of orders.
     */
    public static function add_session_order(Order $order)
    {
        $history = self::get_session_order_ids();
        if (!is_array($history)) {
            $history = array();
        }
        $history[$order->ID] = $order->ID;
        ShopTools::getSession()->set(self::$sessname, $history);
    }

    /**
     * Get historical orders for current session.
     */
    public static function get_session_order_ids()
    {
        $history = ShopTools::getSession()->get(self::$sessname);
        if (!is_array($history)) {
            $history = null;
        }
        return $history;
    }

    public static function clear_session_order_ids()
    {
        ShopTools::getSession()->set(self::$sessname, null)->clear(self::$sessname);
    }

    /**
     * Get the order via url 'ID' or form submission 'OrderID'.
     * It will check for permission based on session stored ids or member id.
     *
     * @return Order order
     */
    public function orderfromid()
    {
        $request = $this->owner->getRequest();
        $id = (int)$request->param('ID');
        if (!$id) {
            $id = (int)$request->postVar('OrderID');
        }

        return $this->allorders()->byID($id);
    }

    /**
     * Get all orders for current member / session.
     *
     * @return DataList of Orders
     */
    public function allorders()
    {
        $filters = array(
            'ID' => -1 //ensures no results are returned
        );
        if ($sessids = self::get_session_order_ids()) {
            $filters['ID'] = $sessids;
        }
        if ($member = Security::getCurrentUser()) {
            $filters['MemberID'] = $member->ID;
        }

        return Order::get()->filterAny($filters)
            ->filter('Status:not', Order::config()->hidden_status);
    }

    /**
     * Return all past orders for current member / session.
     */
    public function PastOrders($paginated = false)
    {
        $orders = $this->allorders()
            ->filter('Status', Order::config()->placed_status);
        if ($paginated) {
            $orders = PaginatedList::create($orders, $this->owner->getRequest());
        }

        return $orders;
    }

    /**
     * Return the {@link Order} details for the current
     * Order ID that we're viewing (ID parameter in URL).
     *
     * @param  HTTPRequest $request
     * @return array of template variables
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function order(HTTPRequest $request)
    {
        //move the shopping cart session id to past order ids, if it is now an order
        ShoppingCart::singleton()->archiveorderid($request->param('ID'));

        $order = $this->orderfromid();
        if (!$order) {
            return $this->owner->httpError(404, 'Order could not be found');
        }

        return array(
            'Order' => $order,
            'Form' => $this->ActionsForm() //see OrderManipulation extension
        );
    }

    /**
     * Build a form for cancelling, or retrying payment for a placed order.
     *
     * @return Form
     */
    public function ActionsForm()
    {
        if ($order = $this->orderfromid()) {
            $form = OrderActionsForm::create($this->owner, 'ActionsForm', $order);
            $form->extend('updateActionsForm', $order);
            if (!$form->Actions()->exists()) {
                return null;
            }

            return $form;
        }
        return null;
    }

    protected $sessionmessage;

    protected $sessionmessagetype = null;

    public function setSessionMessage($message = 'success', $type = 'good')
    {
        $this->owner->getRequest()->getSession()
            ->set('OrderManipulation.Message', $message)
            ->set('OrderManipulation.MessageType', $type);
    }

    public function SessionMessage()
    {
        $session = $this->owner->getRequest()->getSession();
        if ($session && ($message = $session->get('OrderManipulation.Message'))) {
            $this->sessionmessage = $message;
            $session->clear('OrderManipulation.Message');
        }

        return $this->sessionmessage;
    }

    public function SessionMessageType()
    {
        $session = $this->owner->getRequest()->getSession();
        if ($session && ($type = $session->get('OrderManipulation.MessageType'))) {
            $this->sessionmessagetype = $type;
            $session->clear('OrderManipulation.MessageType');
        }

        return $this->sessionmessagetype;
    }
}
