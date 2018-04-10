# Set orders as the default admin panel

Add the following to your yaml config:

```yml
SilverStripe\Admin\AdminRootController:
  default_panel: 'SilverShop\Admin\OrdersAdmin'
```
