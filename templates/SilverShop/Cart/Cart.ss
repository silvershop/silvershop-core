<% if $Items %>
    <table class="silvershop-cart" summary="<%t SilverShop\Cart\ShoppingCart.TableSummary "Current contents of your cart." %>">
        <colgroup>
            <col class="silvershop-cart__col silvershop-cart__col--image"/>
            <col class="silvershop-cart__col silvershop-cart__col--product"/>
            <col class="silvershop-cart__col silvershop-cart__col--unit-price" />
            <col class="silvershop-cart__col silvershop-cart__col--quantity" />
            <col class="silvershop-cart__col silvershop-cart__col--total"/>
            <col class="silvershop-cart__col silvershop-cart__col--remove"/>
        </colgroup>
        <thead class="silvershop-cart__head">
            <tr class="silvershop-cart__head-row">
                <th class="silvershop-cart__head-cell silvershop-cart__head-cell--image" scope="col"></th>
                <th class="silvershop-cart__head-cell silvershop-cart__head-cell--product" scope="col"><%t SilverShop\Page\Product.SINGULARNAME "Product" %></th>
                <th class="silvershop-cart__head-cell silvershop-cart__head-cell--unit-price" scope="col"><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></th>
                <th class="silvershop-cart__head-cell silvershop-cart__head-cell--quantity" scope="col"><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
                <th class="silvershop-cart__head-cell silvershop-cart__head-cell--total" scope="col"><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></th>
                <% if $Editable %>
                    <th class="silvershop-cart__head-cell silvershop-cart__head-cell--remove" scope="col"><%t SilverShop\Generic.Remove "Remove" %></th>
                <% end_if %>
            </tr>
        </thead>
        <tbody class="silvershop-cart__body">
            <% loop $Items %><% if $ShowInTable %>
                <tr class="silvershop-cart__row silvershop-cart__row--item $EvenOdd $FirstLast">
                    <td class="silvershop-cart__cell silvershop-cart__cell--image">
                        <% if $Image %>
                            <div class="silvershop-cart__image">
                                <a class="silvershop-cart__image-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">
                                    $Image.ScaleWidth(45)
                                </a>
                            </div>
                        <% end_if %>
                    </td>
                    <td class="silvershop-cart__cell silvershop-cart__cell--product">
                        <h3 class="silvershop-cart__product-title">
                        <% if $Link %>
                            <a class="silvershop-cart__product-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
                        <% else %>
                            $TableTitle
                        <% end_if %>
                        </h3>
                        <% if $SubTitle %><p class="silvershop-cart__subtitle">$SubTitle</p><% end_if %>
                        <% if $Product.Variations && $Up.Editable %>
                            <span class="silvershop-cart__variation-label"><%t SilverShop\Generic.Change "Change" %>:</span> $VariationField
                        <% end_if %>
                    </td>
                    <td class="silvershop-cart__cell silvershop-cart__cell--unit-price">$UnitPrice.Nice</td>
                    <td class="silvershop-cart__cell silvershop-cart__cell--quantity"><% if $Up.Editable %>$QuantityField<% else %>$Quantity<% end_if %></td>
                    <td class="silvershop-cart__cell silvershop-cart__cell--total">$Total.Nice</td>
                    <% if $Up.Editable %>
                        <td class="silvershop-cart__cell silvershop-cart__cell--remove">
                            <% if $RemoveField %>
                                $RemoveField
                            <% else %>
                                <a class="silvershop-cart__remove-link" href="$removeAllLink" title="<%t SilverShop\Cart\ShoppingCart.RemoveAllTitle "Remove all of &quot;{Title}&quot; from your cart" Title=$TableTitle %>">
                                    <img class="silvershop-cart__remove-icon" src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="x"/>
                                </a>
                            <% end_if %>

                        </td>
                    <% end_if %>
                </tr>
            <% end_if %><% end_loop %>
        </tbody>
        <tfoot class="silvershop-cart__foot">
            <tr class="silvershop-cart__row silvershop-cart__row--subtotal">
                <th class="silvershop-cart__cell silvershop-cart__cell--subtotal-label" colspan="4" scope="row"><%t SilverShop\Model\Order.SubTotal "Sub-total" %></th>
                <td class="silvershop-cart__cell silvershop-cart__cell--subtotal-value">$SubTotal.Nice</td>
                <% if $Editable %><td class="silvershop-cart__cell silvershop-cart__cell--spacer">&nbsp;</td><% end_if %>
            </tr>
            <% if $ShowSubtotals %>
                <% if $Modifiers %>
                    <% loop $Modifiers %>
                        <% if $ShowInTable %>
                            <tr class="silvershop-cart__row silvershop-cart__row--modifier $Classes">
                                <th class="silvershop-cart__cell silvershop-cart__cell--modifier-label" colspan="4" scope="row">
                                    <% if $Link %>
                                        <a class="silvershop-cart__modifier-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
                                    <% else %>
                                        $TableTitle
                                    <% end_if %>
                                </th>
                                <td class="silvershop-cart__cell silvershop-cart__cell--modifier-value">$TableValue.Nice</td>
                                <% if $Up.Editable %>
                                    <td class="silvershop-cart__cell silvershop-cart__cell--remove">
                                        <% if $CanRemove %>
                                            <strong>
                                                <a class="silvershop-cart__remove-link silvershop-cart__remove-link--modifier" href="$removeLink" title="<%t SilverShop\Cart\ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>">
                                                    <img class="silvershop-cart__remove-icon" src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="x"/>
                                                </a>
                                            </strong>
                                        <% end_if %>
                                    </td>
                                <% end_if %>
                            </tr>
                            <% if $Form %>
                                <tr class="silvershop-cart__row silvershop-cart__row--modifier-form">
                                    <td class="silvershop-cart__cell silvershop-cart__cell--modifier-form" colspan="5">$Form</td><td colspan="10"></td>
                                </tr>
                            <% end_if %>
                        <% end_if %>
                    <% end_loop %>
                <% end_if %>
                <tr class="silvershop-cart__row silvershop-cart__row--total">
                    <th class="silvershop-cart__cell silvershop-cart__cell--total-label" colspan="4" scope="row"><%t SilverShop\Model\Order.Total "Total" %></th>
                    <td class="silvershop-cart__cell silvershop-cart__cell--total-value"><span class="silvershop-cart__total-amount">$Total.Nice</span> <span class="silvershop-cart__total-currency">$Currency</span></td>
                    <% if $Editable %><td class="silvershop-cart__cell silvershop-cart__cell--spacer">&nbsp;</td><% end_if %>
                </tr>
            <% end_if %>
        </tfoot>
    </table>
<% else %>
    <p class="silvershop-message silvershop-message--warning">
        <%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %>
    </p>
<% end_if %>
