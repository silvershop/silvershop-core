<table id="InformationTable" class="infotable ordercontent">
    <colgroup class="image"/>
    <colgroup class="product title"/>
    <colgroup class="unitprice" />
    <colgroup class="quantity" />
    <colgroup class="total"/>
    <thead>
        <tr>
            <th scope="col"></th>
            <th scope="col"><%t Product.SINGULARNAME "Product" %></th>
            <th class="center" scope="col"><%t Order.UnitPrice "Unit Price" %></th>
            <th class="center" scope="col"><%t Order.Quantity "Quantity" %></th>
            <th class="right" scope="col"><%t Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></th>
        </tr>
    </thead>
    <tbody>
        <% loop $Items %>
            <% include Order_Content_ItemLine %>
        <% end_loop %>
    </tbody>
    <% include Order_Content_SubTotals %>
</table>
