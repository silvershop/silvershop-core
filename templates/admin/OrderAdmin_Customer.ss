<table class="order-customer ss-gridfield-table">
    <thead>
        <tr class="title">
            <th colspan="2">
                <h2><%t Shop.Customer "Customer" %></h2>
            </th>
        </tr>
        <tr class="header">
            <th class="main"><%t AccountPage.MemberName "Name" %></th>
            <th class="main"><%t AccountPage.MemberEmail "Email" %></th>
        </tr>
    </thead>
    <tbody>
        <tr class="ss-gridfield-item">
            <td>$Name</td>
            <td>
                <% if $LatestEmail %>
                    <a href="mailto:$LatestEmail">$LatestEmail</a>
                <% end_if %>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td class="bottom-all" colspan="5"></td>
        </tr>
    </tfoot>
</table>
