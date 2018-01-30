<table class="order-addresses ss-gridfield-table">
    <thead>
        <tr class="title">
            <th colspan="2">
                <h2><%t Address.SINGULARNAME "Address" %></h2>
            </th>
        </tr>
        <tr class="header">
            <th class="main"><%t Order.ShipTo "Ship To" %></th>
            <th class="main"><%t Order.BillTo "Bill To" %></th>
        </tr>
    </thead>
    <tbody>
        <tr class="ss-gridfield-item ">
            <td>$getShippingAddress</td>
            <td>$getBillingAddress</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td class="bottom-all" colspan="5"></td>
        </tr>
    </tfoot>
</table>
