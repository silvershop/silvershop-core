There are a number of manual and automated SilverStripe tasks that can be set up and run.

## Manual Tasks

The manual tasks can be accessed from yoursite/dev/shop.

If you have a large number of dataobjects, it may pay to run these tasks from the command line, for example:

    [rootdir]: framework/sake dev/tasks/CartCleanupTask

### CartCleanupTask

This will remove old carts from the database to help keep the number of carts down. You can specify the age of carts
in days to clear from (default is 90 days old). 

### CustomersToGroupTask

Adds members who have placed orders to the selected customer group (see the shop config). Useful for maintaining a distinction between shop customers and other members.

## Automated Tasks

These tasks are intended to be [run via cron jobs](http://doc.silverstripe.org/framework/en/topics/commandline#running-regular-tasks-with-cron).

### Delete old carts

Because carts are stored in the database, you will probably want to automatically or manually clear out old ones.
The best way to do this is via the provided 'Delete Old Carts' / CartCleanup Task.

To run manually, in your browser visit:

	[yoursiteurl.dom]/dev/tasks/CartCleanupTask

To run automatically, trigger the following [sake script](http://doc.silverstripe.org/framework/en/topics/commandline) to run periodically on your sever:

	[yoursitepath]/framework/sake dev/tasks/CartCleanupTask

If you add '?type=sql' to the end of the url, the deletion will use direct SQL satements, which is faster, but less secure.