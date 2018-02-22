<tfoot class="shop-order__subtotals">
    <tr>
        <th colspan="4"><%t SilverShop\Model\Order.SubTotal "Sub-total" %></th>
        <th>$SubTotal.Nice</th>
    </tr>
    <% loop $Modifiers %>
        <% if $ShowInTable %>
            <tr class="$EvenOdd $FirstLast $Classes">
                <td colspan="4">
                    $TableTitle
                    <% if $SubTitle %><small class="shop-order__subtitle">($SubTitle)</small><% end_if %>
                </td>
                <td>$TableValue.Nice</td>
            </tr>
        <% end_if %>
    <% end_loop %>
    <tr class="shop-order__total">
        <th colspan="4"><%t SilverShop\Model\Order.Total "Total" %></th>
        <th>$Total.Nice $Currency</th>
    </tr>
    <% if $TotalOutstanding %>
        <tr>
            <td colspan="4"><%t SilverShop\Model\Order.Outstanding "Outstanding" %></td>
            <td>$TotalOutstanding.Nice $Currency</td>
        </tr>
    <% end_if %>
    <tr>
        <td class="bottom-all" colspan="5"></td>
    </tr>
</tfoot>
