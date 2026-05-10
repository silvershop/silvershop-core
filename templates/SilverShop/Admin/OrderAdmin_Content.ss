<div class="silvershop-grid silvershop-grid-field">
    <table class="silvershop-shop-order silvershop-shop-order--content silvershop-grid-field__table">
        <thead>
            <tr class="silvershop-shop-order__title-row">
                <th class="silvershop-shop-order__title-cell" colspan="5">
                    <h2 class="silvershop-shop-order__title"><%t SilverShop\Model\OrderItem.PLURALNAME "Items" %></h2>
                </th>
            </tr>
            <tr class="silvershop-shop-order__header">
                <th class="silvershop-shop-order__header-cell silvershop-shop-order__header-cell--image"></th>
                <th class="silvershop-shop-order__header-cell silvershop-shop-order__header-cell--product"><span><%t SilverShop\Page\Product.SINGULARNAME "Product" %></span></th>
                <th class="silvershop-shop-order__header-cell silvershop-shop-order__header-cell--unit-price"><span><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></span></th>
                <th class="silvershop-shop-order__header-cell silvershop-shop-order__header-cell--quantity"><span><%t SilverShop\Model\Order.Quantity "Quantity" %></span></th>
                <th class="silvershop-shop-order__header-cell silvershop-shop-order__header-cell--total"><span><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></span></th>
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
