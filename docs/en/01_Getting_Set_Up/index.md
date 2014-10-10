This tutorial explains start-to-finish how to set up an online shop with the SilverStripe shop module. If you are upgrading your shop, see [Upgrading](Getting_Set_Up/Upgrading).

## SilverStripe

Follow the standard [SilverStripe installation guide](http://doc.silverstripe.org/framework/en/installation/) to get a SilverStripe website set up.

## Install Shop

[Install the shop module](Getting_Set_Up/Installation).

## Shipping and Tax

 * [Configure shipping](Getting_Set_Up/Shipping)
 * [Configure taxes](Getting_Set_Up/Tax)

## Payment

[Set up your payment provider](Getting_Set_Up/Payment), so customers can make online payments.

## Automated Tasks

Add some [automated tasks](Getting_Set_Up/Tasks) to handle some things automatically for you.

## Bulk Loading Products

[Products can be bulk loaded](Getting_Set_Up/Bulk_Loading), saving time on larger websites.

## Testing / Development Environment
Useful development tools are accessible via [yoursite]/dev/shop.

### Debugging

If you are wanting to use a debugger tool, you'll probably need to make sure you have an index.php file, which can be found in the SilverStripe installer. Point your debugger to use index.php, as it likely wont be able to handle using htaccess configurations.

### E-Mails

To catch local emails, you either need to set up a local dummy SMTP server, or...

 * Windows - you can run the "Antix SMTP Server For Developers", and open the emails in your preferred email client.
 * Linux,Mac - pipe emails to a custom php script, such as [this one](http://blogs.bigfish.tv/adam/2009/12/03/setup-a-testing-mail-server-using-php-on-mac-os-x/).