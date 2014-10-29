<table class="table table-bordered orderhistory">

    <thead>

    <tr>
        <th><%t OrderHistory.OrderReference 'Reference' %></th>
        <th><%t OrderHistory.OrderDate 'Date' %></th>
        <th><%t OrderHistory.OrderItems 'Items' %></th>
        <th><%t OrderHistory.OrderTotal 'Total' %></th>
        <th><%t OrderHistory.OrderStatus 'Status' %></th>
        <th></th>
    </tr>

    </thead>

    <tbody>

        <% loop $PastOrders %>

        <tr class="{$Status}">
            <td>$Reference</td>
            <td>$Created.Nice</td>
            <td>$Items.Quantity</td>
            <td>$Total.Nice</td>
            <td>$Status</td>
            <td>
                <a class="btn btn-mini btn-primary" href="$Link">
                    <i class="icon icon-white icon-eye-open"></i> <%t OrderHistory.ViewOrder 'view' %>
                </a>
            </td>
        </tr>

        <% end_loop %>

    </tbody>

</table>