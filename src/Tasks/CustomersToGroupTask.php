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

    public function run($request)
    {

        $gp = ShopConfigExtension::current()->CustomerGroup();
        if (empty($gp)) {
            return false;
        }

        $allCombos = DB::query(
            'SELECT "ID", "MemberID", "GroupID" FROM "Group_Members" WHERE "Group_Members"."GroupID" = '
            . $gp->ID . ';'
        );
        //make an array of all combos
        $alreadyAdded = array();
        $alreadyAdded[-1] = -1;
        if ($allCombos) {
            foreach ($allCombos as $combo) {
                $alreadyAdded[$combo['MemberID']] = $combo['MemberID'];
            }
        }
        $unlistedMembers = DataObject::get(
            Member::class,
            $where = '"Member"."ID" NOT IN (' . implode(',', $alreadyAdded) . ')',
            $sort = null,
            $join = 'INNER JOIN "SilverShop_Order" ON "SilverShop_Order"."MemberID" = "Member"."ID"'
        );
        //add combos
        if ($unlistedMembers) {
            $existingMembers = $gp->Members();
            foreach ($unlistedMembers as $member) {
                $existingMembers->add($member);
                echo '.';
            }
        } else {
            echo 'no new members added';
        }
    }
}
