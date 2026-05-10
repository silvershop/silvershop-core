<table class="silvershop-receipt silvershop-receipt--content">
    <colgroup class="silvershop-receipt__col silvershop-receipt__col--image"/>
    <colgroup class="silvershop-receipt__col silvershop-receipt__col--product"/>
    <colgroup class="silvershop-receipt__col silvershop-receipt__col--unit-price" />
    <colgroup class="silvershop-receipt__col silvershop-receipt__col--quantity" />
    <colgroup class="silvershop-receipt__col silvershop-receipt__col--total"/>
    <thead>
        <tr class="silvershop-receipt__row silvershop-receipt__row--head">
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head" scope="col"></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head" scope="col"><%t SilverShop\Page\Product.SINGULARNAME "Product" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--center" scope="col"><%t SilverShop\Model\Order.UnitPrice "Unit Price" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--center" scope="col"><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--right" scope="col"><%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %></th>
        </tr>
    </thead>
    <tbody>
        <% loop $Items %>
            <% include SilverShop\Model\Order_Content_ItemLine %>
        <% end_loop %>
    </tbody>
    <% include SilverShop\Model\Order_Content_SubTotals %>
</table>
