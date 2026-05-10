<% if $Items %>
    <table class="silvershop-cart" summary="<%t SilverShop\Cart\ShoppingCart.TableSummary "Current contents of your cart." %>">
        <colgroup>
            <col class="silvershop-image"/>
            <col class="silvershop-product silvershop-title"/>
            <col class="silvershop-unitprice" />
            <col class="silvershop-quantity" />
            <col class="silvershop-total"/>
            <col class="silvershop-remove"/>
        </colgroup>
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col"><%t SilverShop\Page\Product.SINGULARNAME "Product" %></th>
                <th scope="col"><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></th>
                <th scope="col"><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
                <th scope="col"><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></th>
                <% if $Editable %>
                    <th scope="col"><%t SilverShop\Generic.Remove "Remove" %></th>
                <% end_if %>
            </tr>
        </thead>
        <tbody>
            <% loop $Items %><% if $ShowInTable %>
                <tr id="$TableID" class="$Classes $EvenOdd $FirstLast">
                    <td>
                        <% if $Image %>
                            <div class="silvershop-image">
                                <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">
                                    $Image.ScaleWidth(45)
                                </a>
                            </div>
                        <% end_if %>
                    </td>
                    <td id="$TableTitleID">
                        <h3>
                        <% if $Link %>
                            <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
                        <% else %>
                            $TableTitle
                        <% end_if %>
                        </h3>
                        <% if $SubTitle %><p class="silvershop-subtitle">$SubTitle</p><% end_if %>
                        <% if $Product.Variations && $Up.Editable %>
                            <%t SilverShop\Generic.Change "Change" %>: $VariationField
                        <% end_if %>
                        <% if $Up.Editable %>
                            $CommentField
                        <% else_if $Comment %>
                            <p class="silvershop-comment">$Comment.XML</p>
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
                                <a href="$removeAllLink" title="<%t SilverShop\Cart\ShoppingCart.RemoveAllTitle "Remove all of &quot;{Title}&quot; from your cart" Title=$TableTitle %>">
                                    <img src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="x"/>
                                </a>
                            <% end_if %>

                        </td>
                    <% end_if %>
                </tr>
            <% end_if %><% end_loop %>
        </tbody>
        <tfoot>
            <tr class="silvershop-subtotal">
                <th colspan="4" scope="row"><%t SilverShop\Model\Order.SubTotal "Sub-total" %></th>
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
                                        <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
                                    <% else %>
                                        $TableTitle
                                    <% end_if %>
                                </th>
                                <td id="$TableTotalID">$TableValue.Nice</td>
                                <% if $Up.Editable %>
                                    <td>
                                        <% if $CanRemove %>
                                            <strong>
                                                <a class="silvershop-ajaxQuantityLink" href="$removeLink" title="<%t SilverShop\Cart\ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>">
                                                    <img src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="x"/>
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
                <tr class="silvershop-gap silvershop-Total">
                    <th colspan="4" scope="row"><%t SilverShop\Model\Order.Total "Total" %></th>
                    <td id="$TableTotalID"><span class="silvershop-value">$Total.Nice</span> <span class="silvershop-currency">$Currency</span></td>
                    <% if $Editable %><td>&nbsp;</td><% end_if %>
                </tr>
            <% end_if %>
        </tfoot>
    </table>
<% else %>
    <p class="silvershop-message silvershop-warning">
        <%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %>
    </p>
<% end_if %>
