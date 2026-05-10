<tfoot class="silvershop-shop-order__subtotals">
    <tr class="silvershop-shop-order__row silvershop-shop-order__row--subtotal">
        <th class="silvershop-shop-order__cell silvershop-shop-order__cell--label" colspan="4"><%t SilverShop\Model\Order.SubTotal "Sub-total" %></th>
        <th class="silvershop-shop-order__cell silvershop-shop-order__cell--value">$SubTotal.Nice</th>
    </tr>
    <% loop $Modifiers %>
        <% if $ShowInTable %>
            <tr class="silvershop-shop-order__row silvershop-shop-order__row--modifier $EvenOdd $FirstLast $Classes">
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--label" colspan="4">
                    $TableTitle
                    <% if $SubTitle %><small class="silvershop-shop-order__subtitle">($SubTitle)</small><% end_if %>
                </td>
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--value">$TableValue.Nice</td>
            </tr>
        <% end_if %>
    <% end_loop %>
    <tr class="silvershop-shop-order__row silvershop-shop-order__row--total">
        <th class="silvershop-shop-order__cell silvershop-shop-order__cell--label" colspan="4"><%t SilverShop\Model\Order.Total "Total" %></th>
        <th class="silvershop-shop-order__cell silvershop-shop-order__cell--value">$Total.Nice $Currency</th>
    </tr>
    <% if $TotalOutstanding %>
        <tr class="silvershop-shop-order__row silvershop-shop-order__row--outstanding">
            <td class="silvershop-shop-order__cell silvershop-shop-order__cell--label" colspan="4"><%t SilverShop\Model\Order.Outstanding "Outstanding" %></td>
            <td class="silvershop-shop-order__cell silvershop-shop-order__cell--value">$TotalOutstanding.Nice $Currency</td>
        </tr>
    <% end_if %>
    <tr class="silvershop-shop-order__row silvershop-shop-order__row--bottom">
        <td class="silvershop-shop-order__cell silvershop-shop-order__cell--bottom" colspan="5"></td>
    </tr>
</tfoot>
