Shop emails can be customised to suit your project needs.

## Enable / disable sending of emails

There are a few yaml config options that will affect which emails are sent:

```yaml
SilverShop\Model\Order:
  send_receipt: false # to disable the emailing of paid invoices (default is true)

SilverShop\Checkout\OrderProcessor:
  #send order confirmation when order is placed, but unpaid
  send_confirmation: true

#send a bcc copy of emails to administrator
SilverShop\Checkout\OrderEmailNotifier:
  bcc_confirmation_to_admin: true
  bcc_receipt_to_admin: true
  bcc_status_change_to_admin: true

#Specify the 'from' address to use in email correspondence
SilverShop\Extension\ShopConfigExtension:
  email_from: store@website.com
```

## Modifying templates

Override email templates in the `templates/SilverShop/Model/` folder by creating a corresponding folder/template in your theme.  For example, `{yourtheme}/templates/SilverShop/Model/Order_StatusEmail.ss`, or place in `app/templates/SilverShop/Model/Order_StatusEmail.ss`.

## Overriding Subjects & Titles

Email subjects & titles are in the translation system, so you can do the following to change an email subject line:

```yaml
#in mysite/lang/en.yml
en:
  SilverShop\ShopEmail:
    ConfirmationTitle: "My Website Order # {OrderNo}"
    ConfirmationSubject: 'My Website Order #{OrderNo} confirmation'
    ReceiptSubject: 'My Website Order #{OrderNo} receipt'
    CancelSubject: 'My Website Order #{OrderNo} cancelled by member'
```

## Making your own Notifier

There may be times when you want to add custom logic around the email. For instance if your purchase requires your user to enter email addresses and each of those addresses receives a different confirmation email. In this situation make a `MyCustomOrderEmailNotifier` that can optionally extend `OrderEmailNotifier` and add custom logic into it then declare it to replace the current notifier.

```yaml
# in mysite/config.yml
Injector:
  SilverShop\Checkout\OrderEmailNotifier:
    class: MyCustomOrderEmailNotifier
```
