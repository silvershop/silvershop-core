<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><%t SilverShop\ShopEmail.StatusChangeTitle "Shop Status Change" %></title>
        <% include SilverShop\Includes\OrderReceiptStyle %>
    </head>
    <body>
        <table class="silvershop-email silvershop-email--status" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <table class="silvershop-email__content" cellspacing="0" cellpadding="0" summary="Email Information">
                        <thead>
                            <tr>
                                <th class="silvershop-email__title-cell" scope="col" colspan="2">
                                    <span class="silvershop-email__brand">$SiteConfig.Title</span>
                                    <h1 class="silvershop-email__title"><%t SilverShop\ShopEmail.StatusChangeTitle "Shop Status Change" %></h1>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <% with $Order %>
                                <tr>
                                    <td class="silvershop-email__intro silvershop-typography" colspan="2">
                                        <%t SilverStripe\Control\ChangePasswordEmail_ss.Hello 'Hello' %> <% if $FirstName %>$FirstName<% else %>$Member.FirstName<% end_if %>,<br /><br />
                                        <%t SilverShop\ShopEmail.StatusChanged 'Status for order #{OrderNo} changed to "{OrderStatus}"' OrderNo=$Reference OrderStatus=$StatusI18N %>
                                    </td>
                                </tr>
                            <% end_with %>
                            <% if $Note %>
                                <tr>
                                    <td class="silvershop-email__intro silvershop-typography" colspan="2">$Note</td>
                                </tr>
                            <% end_if %>
                            <tr>
                                <td class="silvershop-email__intro silvershop-typography" colspan="2">
                                    <%t SilverShop\ShopEmail.Regards "Kind regards" %><br />$SiteConfig.Title
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
