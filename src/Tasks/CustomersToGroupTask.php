<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Security\Member;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Adds all customers to an assigned group.
 *
 * @package    shop
 * @subpackage tasks
 */
class CustomersToGroupTask extends BuildTask
{
    protected string $title = 'Customers to Group';

    protected static string $description = 'Adds all customers to an assigned group.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $gp = ShopConfigExtension::current()->CustomerGroup();
        if (!$gp->exists()) {
            $output->writeln(
                _t(
                    'SilverShop\Task\CustomersToGroupTask.DefaultCustomerGroupRequired',
                    'Default Customer Group required'
                )
            );
            return Command::FAILURE;
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
                $output->write('.');
            }
            $output->writeln('');
        } else {
            $output->writeln(
                _t(
                    'SilverShop\Task\CustomersToGroupTask.NoNewMembersAdded',
                    'No new members added'
                )
            );
        }
        return Command::SUCCESS;
    }
}
