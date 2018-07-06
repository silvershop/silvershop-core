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

Update email content by overriding the templates inside the `templates/email/` folder. Just create a corresponding folder/template in your mysite folder, such as `mysite/templates/email/Order_RecieptEmail`.

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

There may be times when you want to add custom logic around the e-mail. For instance if your purchase requires your user to enter e-mail addresses and each of those addresses receives a different confirmation e-mail. In this situation make a `MyCustomOrderEmailNotifier` that can optionally extend `OrderEmailNotifier` and add custom logic into it then declare it to replace the current notifier

```yaml
# in mysite/config.yml
Injector:
  OrderEmailNotifier:
    class: MyCustomOrderEmailNotifier
```

