<% include SilverShop\Includes\OrderEmailHeader Modifier="confirmation" %>
                        <tr>
                            <th class="silvershop-email__title-cell" scope="col" colspan="2">
                                <span class="silvershop-email__brand">$SiteConfig.Title</span>
                                <h1 class="silvershop-email__title"><%t SilverShop\ShopEmail.ConfirmationTitle "Order Confirmation" %></h1>
                            </th>
                        </tr>
                        <% if $PurchaseCompleteMessage %>
                        <tr>
                            <td class="silvershop-email__intro silvershop-typography" colspan="2">$PurchaseCompleteMessage</td>
                        </tr>
                        <% end_if %>
                        <% if $Order %>
                        <% with $Order %>
                        <tr>
                            <td class="silvershop-email__order" colspan="2"><% include SilverShop\Model\Order %></td>
                        </tr>
                        <% end_with %>
                        <% end_if %>
<% include SilverShop\Includes\OrderEmailFooter %>
