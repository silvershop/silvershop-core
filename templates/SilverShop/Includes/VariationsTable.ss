<table class="silvershop-variations-table">
    <thead class="silvershop-variations-table__head">
        <tr class="silvershop-variations-table__row silvershop-variations-table__row--head">
            <th class="silvershop-variations-table__cell silvershop-variations-table__cell--variation"><%t SilverShop\Model\ProductVariation.SINGULARNAME "Variation" %></th>
            <th class="silvershop-variations-table__cell silvershop-variations-table__cell--price"><%t SilverShop\Page\Product.Price "Price" %></th>
            <% if $canPurchase %>
                <th class="silvershop-variations-table__cell silvershop-variations-table__cell--quantity"><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
            <% end_if %>
        </tr>
    </thead>
    <tbody class="silvershop-variations-table__body">
        <% loop $Variations %>
            <tr class="silvershop-variations-table__row">
                <td class="silvershop-variations-table__cell silvershop-variations-table__cell--variation">$Title.XML</td>
                <td class="silvershop-variations-table__cell silvershop-variations-table__cell--price">$Price.Nice $Currency</td>
                <td class="silvershop-variations-table__cell silvershop-variations-table__cell--quantity">
                <% if $canPurchase %>
                    <% if $IsInCart %>
                        <% with $Item %>
                            $QuantityField
                        <% end_with %>
                    <% else %>
                        <a class="silvershop-variations-table__add-link" href="$Item.addLink" title="<%t SilverShop\Page\Product.AddToCartTitle "Add &quot;{Title}&quot; to your cart" Title=$Title.XML %>">
                            <%t SilverShop\Page\Product.AddToCart "Add to Cart" %>
                        </a>
                    <% end_if %>

                <% end_if %>
                </td>
            </tr>
        <% end_loop %>
    </tbody>
</table>
