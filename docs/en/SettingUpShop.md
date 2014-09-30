# Setting up Shop

<div class="bad" markdown="1">
The documentation for this topic is incomplete. Please comment below to indicate you need it.
</div>


This tutorial explains start-to-finish how to set up an online shop with the SilverStripe shop module.
If you are upgrading your shop, see [Upgrading](Upgrading).

## Web server and SilverStripe installation

Follow the standard [SilverStripe installation guide](http://doc.silverstripe.org/framework/en/installation/)

## Install Shop & Payment modules

Download the [shop module](http://ss-shop.org), and [payment module](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-omnipay).

Extract the folders into your SilverStripe root directory. Your directory structure should look something like this:

	web-root/
			/cms
			/mysite
			/payment
			/sapphire
			/shop


## Shipping and Tax setup

http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop-shipping


## Choose payment types

See [Payment](Payment)

## Set up automated tasks



### Delete old carts

Because carts are stored in the database, you will probably want to automatically or manually clear out old ones.
The best way to do this is via the provided 'Delete Old Carts' / CartCleanup Task.

To run manually, in your browser visit:

	[yoursiteurl.dom]/dev/tasks/CartCleanupTask

To run automatically, trigger the following [sake script](http://doc.silverstripe.org/framework/en/topics/commandline) to run periodically on your sever:

	[yoursitepath]/framework/sake dev/tasks/CartCleanupTask

If you add '?type=sql' to the end of the url, the deletion will use direct SQL satements, which is faster, but less secure.