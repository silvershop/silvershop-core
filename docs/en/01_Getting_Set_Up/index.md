This tutorial explains start-to-finish how to set up an online shop with the SilverStripe shop module. If you are upgrading your shop, see [Upgrading](01_Getting_Set_Up/02_Upgrading.md).

## SilverStripe

Follow the standard [SilverStripe installation guide](http://docs.silverstripe.org/en/getting_started/installation/) to get a SilverStripe website set up.

## Install Shop

[Install the shop module](01_Getting_Set_Up/01_Installation.md).

## Shipping and Tax

 * [Configure shipping](01_Getting_Set_Up/04_Shipping.md)
 * [Configure taxes](01_Getting_Set_Up/05_Tax.md)

## Payment

[Set up your payment provider](01_Getting_Set_Up/06_Payment.md), so customers can make online payments.

## Automated Tasks

Add some [automated tasks](01_Getting_Set_Up/Tasks.md) to handle some things automatically for you.

## Bulk Loading Products

[Products can be bulk loaded](01_Getting_Set_Up/Bulk_Loading.md), saving time on larger websites.

## Testing / Development Environment
Useful development tools are accessible via `[yoursite]/dev/shop`.

### Debugging

If you are wanting to use a debugger tool, you'll probably need to make sure you have an index.php file, which can be found in the SilverStripe installer. Point your debugger to use index.php, as it likely wont be able to handle using htaccess configurations.

### E-Mails

The best way to catch/debug local emails is to use a service such as [Mailtrap](https://mailtrap.io/) which has a free plan. 

The [silverstripe-email-helpers](https://packagist.org/packages/markguinn/silverstripe-email-helpers) module that will be installed alongside silvershop can be used to send your emails to the mailtrap service. To do so, create a config file `mysite/_config/mailer.yml` with the following content:

```yaml
---
Name: shop-mailer
Only:
  environment: 'dev'
---
SmtpMailer:
  host: 'mailtrap.io'
  user: '<username>'
  password: '<password>'
  encryption: 'tls'
  charset: 'UTF-8'

Injector:
  Mailer:
    class: SmtpMailer
```

After running `dev/build` your emails should now be sent to mailtrap.

Alternatively, you can:

 * Windows - you can run the "Antix SMTP Server For Developers", and open the emails in your preferred email client.
 * Linux,Mac - pipe emails to a custom php script, such as [this one](http://blogs.bigfish.tv/adam/2009/12/03/setup-a-testing-mail-server-using-php-on-mac-os-x/).

