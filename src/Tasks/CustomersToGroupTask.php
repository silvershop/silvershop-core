<?php

namespace SilverShop\Tasks;

use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;

/**
 * Adds all customers to an assigned group.
 *
 * @package    shop
 * @subpackage tasks
 */
class CustomersToGroupTask extends BuildTask
{
    protected $title = 'Customers to Group';

    protected $description = 'Adds all customers to an assigned group.';

    public function run($request): void
    {
        $gp = ShopConfigExtension::current()->CustomerGroup();
        if (!$gp->exists()) {
            die(
                _t(
                    'SilverShop\Task\CustomersToGroupTask.DefaultCustomerGroupRequired',
                    'Default Customer Group required'
                )
            );
        }

        $query = DB::query(
            'SELECT "ID", "MemberID", "GroupID" FROM "Group_Members" WHERE "Group_Members"."GroupID" = '
            . $gp->ID . ';'
        );
        //make an array of all combos
        $alreadyAdded = [];
        $alreadyAdded[-1] = -1;
        if ($query) {
            foreach ($query as $combo) {
                $alreadyAdded[$combo['MemberID']] = $combo['MemberID'];
            }
        }
        $dataList = DataObject::get(
            Member::class,
            $where = '"Member"."ID" NOT IN (' . implode(',', $alreadyAdded) . ')',
            $sort = null,
        )->leftJoin(
            'SilverShop_Order',
            $join = '"SilverShop_Order"."MemberID" = "Member"."ID"'
        );
        //add combos
        if ($dataList) {
            $existingMembers = $gp->Members();
            foreach ($dataList as $member) {
                $existingMembers->add($member);
                echo '.';
            }
        } else {
            echo _t(
                'SilverShop\Task\CustomersToGroupTask.NoNewMembersAdded',
                'No new members added'
            );
        }
    }
}
