<% include SilverShop\Includes\OrderEmailHeader Modifier="admin-notification" %>
                        <tr>
                            <th class="silvershop-email__title-cell" scope="col" colspan="2">
                                <span class="silvershop-email__brand">$SiteConfig.Title</span>
                                <h1 class="silvershop-email__title"><%t SilverShop\ShopEmail.AdminNotificationHeading "New order received" %></h1>
                            </th>
                        </tr>
                        <% if $Order %>
                        <% with $Order %>
                        <tr>
                            <td class="silvershop-email__order" colspan="2"><% include SilverShop\Model\Order %></td>
                        </tr>
                        <% end_with %>
                        <% end_if %>
<% include SilverShop\Includes\OrderEmailFooter %>
