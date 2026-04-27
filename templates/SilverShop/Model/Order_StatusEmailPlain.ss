<%t SilverShop\ShopEmail.StatusChangeTitle 'Shop Status Change' %>

<% with $Order %>
<%t SilverStripe\Control\ChangePasswordEmail_ss.Hello 'Hello' %> <% if $FirstName %>$FirstName<% else %>$Member.FirstName<% end_if %>

<%t SilverShop\ShopEmail.StatusChanged 'Status for order #{OrderNo} changed to "{OrderStatus}"' OrderNo=$Reference OrderStatus=$StatusI18N %>
<% end_with %>

$Note

<%t SilverShop\ShopEmail.Regards "Kind regards" %>

$SiteConfig.Title
$FromEmail
<%t SilverShop\ShopEmail.PhoneNumber "PhoneNumber" %>
