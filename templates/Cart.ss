<% require themedCSS(cart,shop) %>
<% if $Items %>
    <table class="cart" summary="<%t ShoppingCart.TableSummary "Current contents of your cart." %>">
        <colgroup>
            <col class="image"/>
            <col class="product title"/>
            <col class="unitprice" />
            <col class="quantity" />
            <col class="total"/>
            <col class="remove"/>
        </colgroup>
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col"><%t Product.SINGULARNAME "Product" %></th>
                <th scope="col"><%t Order.UnitPrice "Unit Price" %></th>
                <th scope="col"><%t Order.Quantity "Quantity" %></th>
                <th scope="col"><%t Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></th>
                <% if $Editable %>
                    <th scope="col"><%t Shop.Remove "Remove" %></th>
                <% end_if %>
            </tr>
        </thead>
        <tbody>
            <% loop $Items %><% if $ShowInTable %>
                <tr id="$TableID" class="$Classes $EvenOdd $FirstLast">
                    <td>
                        <% if $Image %>
                            <div class="image">
                                <a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">
                                    $Image.setWidth(45)
                                </a>
                            </div>
                        <% end_if %>
                    </td>
                    <td id="$TableTitleID">
                        <h3>
                        <% if $Link %>
                            <a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
                        <% else %>
                            $TableTitle
                        <% end_if %>
                        </h3>
                        <% if $SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
                        <% if $Product.Variations && $Up.Editable %>
                            <%t Shop.Change "Change" %>: $VariationField
                        <% end_if %>
                    </td>
                    <td>$UnitPrice.Nice</td>
                    <td><% if $Up.Editable %>$QuantityField<% else %>$Quantity<% end_if %></td>
                    <td id="$TableTotalID">$Total.Nice</td>
                    <% if $Up.Editable %>
                        <td>
                            <% if $RemoveField %>
                                $RemoveField
                            <% else %>
                                <a href="$removeallLink" title="<%t ShoppingCart.RemoveAllTitle "Remove all of &quot;{Title}&quot; from your cart" Title=$TableTitle %>">
                                    <img src="silvershop/images/remove.gif" alt="x"/>
                                </a>
                            <% end_if %>

                        </td>
                    <% end_if %>
                </tr>
            <% end_if %><% end_loop %>
        </tbody>
        <tfoot>
            <tr class="subtotal">
                <th colspan="4" scope="row"><%t Order.SubTotal "Sub-total" %></th>
                <td id="$TableSubTotalID">$SubTotal.Nice</td>
                <% if $Editable %><td>&nbsp;</td><% end_if %>
            </tr>
            <% if $ShowSubtotals %>
                <% if $Modifiers %>
                    <% loop $Modifiers %>
                        <% if $ShowInTable %>
                            <tr id="$TableID" class="$Classes">
                                <th id="$TableTitleID" colspan="4" scope="row">
                                    <% if $Link %>
                                        <a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
                                    <% else %>
                                        $TableTitle
                                    <% end_if %>
                                </th>
                                <td id="$TableTotalID">$TableValue.Nice</td>
                                <% if $Up.Editable %>
                                    <td>
                                        <% if $CanRemove %>
                                            <strong>
                                                <a class="ajaxQuantityLink" href="$removeLink" title="<%t ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>">
                                                    <img src="silvershop/images/remove.gif" alt="x"/>
                                                </a>
                                            </strong>
                                        <% end_if %>
                                    </td>
                                <% end_if %>
                            </tr>
                            <% if $Form %>
                                <tr>
                                    <td colspan="5">$Form</td><td colspan="10"></td>
                                </tr>
                            <% end_if %>
                        <% end_if %>
                    <% end_loop %>
                <% end_if %>
                <tr class="gap Total">
                    <th colspan="4" scope="row"><%t Order.Total "Total" %></th>
                    <td id="$TableTotalID"><span class="value">$Total.Nice</span> <span class="currency">$Currency</span></td>
                    <% if $Editable %><td>&nbsp;</td><% end_if %>
                </tr>
            <% end_if %>
        </tfoot>
    </table>
<% else %>
    <p class="message warning">
        <%t ShoppingCart.NoItems "There are no items in your cart." %>
    </p>
<% end_if %>
