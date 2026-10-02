<% include SilverShop\Includes\OrderEmailHeader Modifier="status" %>
                        <tr>
                            <th class="silvershop-email__title-cell" scope="col" colspan="2">
                                <span class="silvershop-email__brand">$SiteConfig.Title</span>
                                <h1 class="silvershop-email__title"><%t SilverShop\ShopEmail.StatusChangeTitle "Shop Status Change" %></h1>
                            </th>
                        </tr>
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
<% include SilverShop\Includes\OrderEmailFooter %>
