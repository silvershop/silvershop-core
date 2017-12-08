<table class="table table-bordered orderhistory">

    <thead>

    <tr>
        <th><%t Order.db_Reference 'Reference' %></th>
        <th><%t Order.Date 'Date' %></th>
        <th><%t Order.has_many_Items 'Items' %></th>
        <th><%t Order.Total 'Total' %></th>
        <th><%t Order.db_Status 'Status' %></th>
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
            <td>$StatusI18N</td>
            <td>
                <a class="btn btn-mini btn-primary" href="$Link">
                    <i class="icon icon-white icon-eye-open fa fa-eye"></i> <%t Shop.View 'view' %>
                </a>
            </td>
        </tr>

        <% end_loop %>

    </tbody>

</table>
