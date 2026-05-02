<table id="InformationTable" class="silvershop-infotable silvershop-ordercontent">
    <colgroup class="silvershop-image"/>
    <colgroup class="silvershop-product silvershop-title"/>
    <colgroup class="silvershop-unitprice" />
    <colgroup class="silvershop-quantity" />
    <colgroup class="silvershop-total"/>
    <thead>
        <tr>
            <th scope="col"></th>
            <th scope="col"><%t SilverShop\Page\Product.SINGULARNAME "Product" %></th>
            <th class="silvershop-center" scope="col"><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></th>
            <th class="silvershop-center" scope="col"><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
            <th class="silvershop-right" scope="col"><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></th>
        </tr>
    </thead>
    <tbody>
        <% loop $Items %>
            <% include SilverShop\Model\Order_Content_ItemLine %>
        <% end_loop %>
    </tbody>
    <% include SilverShop\Model\Order_Content_SubTotals %>
</table>
