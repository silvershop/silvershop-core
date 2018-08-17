## Installation
In a terminal window, in the website root, type:
```sh
composer require silvershop/core
```

## Configuration Options
The `example_config.yml` file gives an exhaustive list of the possible configuration options within the Silvershop module.  Copy and paste this to `app/_config/shop.yml` and adjust.  See the [customisation](../02_Customisation/index.md) section of this documentation for more details.

## Email Setup
SilverShop uses SwiftMailer to send emails.  Follow the [SilverStripe Configuration Instructions](https://docs.silverstripe.org/en/4/developer_guides/email/) to setup the smtp mailer.

## Payment
Setup your payment options in `app/_config/payment.yml` similar to the below.
```yaml
---
Name: silvershop-app
---
SilverStripe\Omnipay\Model\Payment:
  allowed_gateways:
    - 'Stripe'
```
See [Payment](../01_Getting_Set_Up/06_Payment.md) for more options.  Please note that you will need to separately `composer require` the [Omnipay Gateway](https://github.com/thephpleague/omnipay#payment-gateways).

## Demo in Localhost
It is always a good idea to have a demo on hand.  Following a new installation, test data can be loaded by visiting `{yoursite.com}/dev/shop`.  A link to a Shop page will now be available in the global navigation.

