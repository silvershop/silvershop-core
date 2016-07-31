<tfoot class="order-content-subtotals">
    <tr class="ss-gridfield-item">
        <th colspan="4" class="main"><%t Order.SubTotal "Sub-total" %></th>
        <th class="main">$SubTotal.Nice</th>
    </tr>
    <% loop $Modifiers %>
        <% if $ShowInTable %>
            <tr class="ss-gridfield-item ss-gridfield-$EvenOdd $FirstLast $Classes">
                <td colspan="4" class="main">
                    $TableTitle
                    <% if $SubTitle %><small class="subtitle">($SubTitle)</small><% end_if %>
                </td>
                <td>$TableValue.Nice</td>
            </tr>
        <% end_if %>
    <% end_loop %>
    <tr class="ss-gridfield-item">
        <th colspan="4" class="main"><%t Order.Total "Total" %></th>
        <th class="main">$Total.Nice $Currency</th>
    </tr>
    <% if $TotalOutstanding %>
        <tr class="ss-gridfield-item">
            <td colspan="4" class="main"><%t Order.Outstanding "Outstanding" %></td>
            <td class="main">$TotalOutstanding.Nice $Currency</td>
        </tr>
    <% end_if %>
    <tr>
        <td class="bottom-all" colspan="5"></td>
    </tr>
</tfoot>
