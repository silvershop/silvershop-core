Shop emails can be customised to suit your project needs.

## Enable / disable sending of emails

There are a few yaml config options that will affect which emails are sent:

```yaml
SilverShop\Checkout\OrderProcessor:
  #send order confirmation when order is placed, but unpaid
  send_confirmation: true

#send a bcc copy of emails to administrator
SilverShop\Checkout\OrderEmailNotifier:
  bcc_confirmation_to_admin: true
  bcc_receipt_to_admin: true

#Specify the 'from' address to use in email correspondence
SilverShop\Extension\ShopConfigExtension:
  email_from: store@website.com

```

## Modifying templates

Update email content by overriding the templates inside the `templates/email/` folder.
Silvershop respects the frontend theme and tries to use those templates.
Just create a corresponding folder/template in your theme or app folder, such as `themes/mytheme/templates/email/Order_RecieptEmail.ss` or `app/templates/email/Order_RecieptEmail.ss`.

Remember: `app/templates` overrules `themes/mytheme/templates`.

## Modifying subject lines

Email subjects are in the translation system, so you can do the following to change an email subject line:


```yaml
#in mysite/lang/en.yml
en:
  ShopEmail:
    ConfirmationSubject: 'My Website Order #{OrderNo} confirmation'
    ReceiptSubject: 'My Website Order #{OrderNo} receipt'
    CancelSubject: 'My Website Order #{OrderNo} cancelled by member'
```

## Making your own Notifier

There may be times when you want to add custom logic around the email. For instance if your purchase requires your user to enter email addresses and each of those addresses receives a different confirmation email. Or if you want to add custom content to the emails sent.

In this situation you can use `OrderEmailNotifier`'s exension hooks to modify all emails, e.g.:

```php
<?php

namespace My\Namespace\Extensions;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Extension;

class ShopEmails extends Extension
{
    public function updateClientEmail(Email $email): void
    {

        $confirmationmessage = 'some text';

        $email->addData([
            'EmailConfirmationMessage' => $confirmationmessage,
        ]);
    }

    public function updateAdminEmail(Email $email): void
    {
        if($replyTo = $this->getOwner()->getOrder()->getLatestEmail()){
            $email->setReplyTo($replyTo);
        }
    }
}
```

Now add this extension to `OrderEmailNotifier` in one of your confing files:

```yaml
SilverShop\Checkout\OrderEmailNotifier:
  extensions:
    depotemails: My\Namespace\Extensions\ShopEmails
```

You can also add your own `sendFooEmail()` methods to that extension.

## Previewing and Testing Emails

Silvershop has a task to preview the generated emails, `SilverShop\Tasks\ShopEmailPreviewTask`.

In case you send customer specific emails (e.g. for sending download links), you can add those to the task's config:

```yaml
SilverShop\Tasks\ShopEmailPreviewTask:
  previewable_emails:
    download: DownloadInformation
```

Now you need to add a method `sendDownloadInformation` to your `ShopEmails` notification, similar to `OrderEmailNotifier`'s `sendConfimation()` or `sendReceipt()` methods.

## See also

*  [Silverstripe CMS Documentation - Email](https://docs.silverstripe.org/en/5/developer_guides/email/)
