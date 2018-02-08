<table class="order-content ss-gridfield-table">
    <thead>
        <tr class="title">
            <th colspan="5">
                <h2><%t SilverShop\Model\OrderItem.PLURALNAME "Items" %></h2>
            </th>
        </tr>
        <tr class="header">
            <th class="main"></th>
            <th class="main"><span class="ui-button-text"><%t SilverShop\Page\Product.SINGULARNAME "Product" %></span></th>
            <th class="main"><span class="ui-button-text"><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></span></th>
            <th class="main"><span class="ui-button-text"><%t SilverShop\Model\Order.Quantity "Quantity" %></span></th>
            <th class="main"><span class="ui-button-text"><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></span></th>
        </tr>
    </thead>
    <tbody>
        <% loop $Items %>
            <% include SilverShop\Admin\OrderAdmin_Content_ItemLine %>
        <% end_loop %>
    </tbody>
    <% include SilverShop\Admin\OrderAdmin_Content_SubTotals %>
</table>
