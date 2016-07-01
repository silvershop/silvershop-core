<table id="ShippingTable" class="infotable">
    <thead>
        <tr>
            <th><%t Order.ShipTo "Ship To" %></th>
            <th><%t Order.BillTo "Bill To" %></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$getShippingAddress</td>
            <td>$getBillingAddress</td>
        </tr>
    </tbody>
</table>
