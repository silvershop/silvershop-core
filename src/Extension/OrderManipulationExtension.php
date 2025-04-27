<?php

namespace SilverShop\Extension;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Forms\OrderActionsForm;
use SilverShop\Model\Order;
use SilverShop\Page\AccountPageController;
use SilverShop\Page\CheckoutPageController;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Security;

/**
 * Provides forms and processing to a controller for editing an
 * order that has been previously placed.
 *
 * @property Controller $owner
 * @extends Extension<((AccountPageController & static) | (CheckoutPageController & static))>
 */
class OrderManipulationExtension extends Extension
{
    use Configurable;

    private static array $allowed_actions = [
        'ActionsForm',
        'order',
    ];

    private static string $sessname = 'OrderManipulation.historicalorders';

    protected string $sessionmessage = '';
    protected string $sessionmessagetype = '';

    /**
     * Add an order to the session-stored history of orders.
     */
    public static function add_session_order(Order $order): void
    {
        $history = self::get_session_order_ids();
        if (!is_array($history)) {
            $history = [];
        }
        $history[$order->ID] = $order->ID;
        ShopTools::getSession()->set(static::config()->get('sessname'), $history);
    }

    /**
     * Get historical orders for current session.
     */
    public static function get_session_order_ids(): ?array
    {
        $history = ShopTools::getSession()->get(static::config()->get('sessname'));
        if (!is_array($history)) {
            return null;
        }
        return $history;
    }

    public static function clear_session_order_ids(): void
    {
        ShopTools::getSession()->set(static::config()->get('sessname'), null)->clear(static::config()->get('sessname'));
    }

    /**
     * Get the order via url 'ID' or form submission 'OrderID'.
     * It will check for permission based on session stored ids or member id.
     */
    public function orderfromid(): ?DataObject
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
     */
    public function allorders(): DataList
    {
        $filters = [
            'ID' => -1 //ensures no results are returned
        ];
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
    public function PastOrders($paginated = false): DataList
    {
        $orders = $this->allorders()
            ->filter('Status', Order::config()->placed_status);
        if ($paginated) {
            return PaginatedList::create($orders, $this->owner->getRequest());
        }

        return $orders;
    }

    /**
     * Return the {@link Order} details for the current
     * Order ID that we're viewing (ID parameter in URL).
     *
     * @return array of template variables
     * @throws HTTPResponse_Exception
     */
    public function order(HTTPRequest $request): array|HTTPResponse
    {
        //move the shopping cart session id to past order ids, if it is now an order
        ShoppingCart::singleton()->archiveorderid($request->param('ID'));

        $order = $this->orderfromid();
        if (!$order) {
            return $this->owner->httpError(404, 'Order could not be found');
        }

        return [
            'Order' => $order,
            'Form' => $this->ActionsForm() //see OrderManipulation extension
        ];
    }

    /**
     * Build a form for cancelling, or retrying payment for a placed order.
     */
    public function ActionsForm(): ?OrderActionsForm
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

    public function setSessionMessage($message = 'success', $type = 'good'): void
    {
        $this->owner->getRequest()->getSession()
            ->set('OrderManipulation.Message', $message)
            ->set('OrderManipulation.MessageType', $type);
    }

    public function SessionMessage(): string
    {
        $session = $this->owner->getRequest()->getSession();
        if ($session && ($message = $session->get('OrderManipulation.Message'))) {
            $this->sessionmessage = $message;
            $session->clear('OrderManipulation.Message');
        }

        return $this->sessionmessage;
    }

    public function SessionMessageType(): string
    {
        $session = $this->owner->getRequest()->getSession();
        if ($session && ($type = $session->get('OrderManipulation.MessageType'))) {
            $this->sessionmessagetype = $type;
            $session->clear('OrderManipulation.MessageType');
        }

        return $this->sessionmessagetype;
    }
}
