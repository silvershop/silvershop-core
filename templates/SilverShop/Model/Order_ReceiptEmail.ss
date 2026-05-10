<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
        <title><%t SilverShop\ShopEmail.ReceiptTitle "Shop Receipt" %></title>
        <% include SilverShop\Includes\OrderReceiptStyle %>
    </head>
    <body>
        <table class="silvershop-email silvershop-email--receipt" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <table class="silvershop-email__content" cellspacing="0" cellpadding="0" summary="Email Information">
                        <thead>
                            <tr>
                                <th class="silvershop-email__title-cell" scope="col" colspan="2">
                                    <h1 class="silvershop-email__title">$Subject</h1>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="silvershop-email__intro silvershop-typography" scope="row" colspan="2">
                                    $PurchaseCompleteMessage
                                </td>
                            </tr>
                            <% if $Order %>
                            <% with $Order %>
                                <tr>
                                    <td class="silvershop-email__order">
                                        <% include SilverShop\Model\Order %>
                                    </td>
                                </tr>
                            <% end_with %>
                            <% end_if %>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
