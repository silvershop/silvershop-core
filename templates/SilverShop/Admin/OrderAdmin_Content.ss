<div class="grid grid-field">
    <table class="shop-order shop-order--content table grid-field__table">
        <thead>
            <tr class="shop-order__title">
                <th colspan="5">
                    <h2><%t SilverShop\Model\OrderItem.PLURALNAME "Items" %></h2>
                </th>
            </tr>
            <tr class="shop-order__header">
                <th></th>
                <th><span><%t SilverShop\Page\Product.SINGULARNAME "Product" %></span></th>
                <th><span><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></span></th>
                <th><span><%t SilverShop\Model\Order.Quantity "Quantity" %></span></th>
                <th><span><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></span></th>
            </tr>
        </thead>
        <tbody>
            <% loop $Items %>
                <% include SilverShop\Admin\OrderAdmin_Content_ItemLine %>
            <% end_loop %>
        </tbody>
        <% include SilverShop\Admin\OrderAdmin_Content_SubTotals %>
    </table>
</div>
