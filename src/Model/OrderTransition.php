<?php

declare(strict_types=1);

namespace SilverShop\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * Immutable, append-only record of a single {@link Order} status transition:
 * the status it moved from, the status it moved to, the transition name/intent
 * (when a caller supplied one via {@link Order::setStatus()}), the acting member
 * and the timestamp. Collectively these form the order's audit trail.
 *
 * Written automatically on every persisted status change (see
 * {@link Order::$audit_status_transitions}). Unlike the legacy
 * {@link OrderStatusLog} — which is opt-in via `log_status`, stores only a
 * human-readable Title/Note, and also emails the customer — this records every
 * transition silently with structured from/to data, so the whole lifecycle can
 * be reconstructed and audited. It never sends email and is never edited or
 * deleted through the ORM.
 *
 * @property string      $FromStatus
 * @property string      $ToStatus
 * @property string|null $TransitionName
 * @property int         $OrderID
 * @property int         $MemberID
 * @method Order Order()
 * @method Member Member()
 */
class OrderTransition extends DataObject
{
    private static string $table_name = 'SilverShop_OrderTransition';

    private static string $plural_name = 'Audit Trail';

    private static array $db = [
        'FromStatus' => 'Varchar(20)',
        'ToStatus' => 'Varchar(20)',
        'TransitionName' => 'Varchar(50)',
    ];

    private static array $has_one = [
        'Order' => Order::class,
        'Member' => Member::class,
    ];

    private static array $indexes = [
        'OrderID' => true,
    ];

    private static string $default_sort = 'ID DESC';

    private static array $summary_fields = [
        'Created' => 'When',
        'FromStatus' => 'From',
        'ToStatus' => 'To',
        'TransitionName' => 'Transition',
        'Member.Title' => 'By',
    ];

    public function canCreate($member = null, $context = []): bool
    {
        return true;
    }

    public function canView($member = null): bool
    {
        $order = $this->Order();

        return $order && $order->exists() ? $order->canView($member) : true;
    }

    public function canEdit($member = null): bool
    {
        return false;
    }

    public function canDelete($member = null): bool
    {
        return false;
    }
}
