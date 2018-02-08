<table class="variationstable">
    <tr>
        <th><%t SilverShop\Model\ProductVariation.SINGULARNAME "Variation" %></th>
        <th><%t SilverShop\Page\Product.Price "Price" %></th>
        <% if $canPurchase %>
            <th><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
        <% end_if %>
    </tr>
    <% loop $Variations %>
            <tr>
                <td>$Title.XML</td>
                <td>$Price.Nice $Currency</td>
                <td>
                <% if $canPurchase %>
                    <% if $IsInCart %>
                        <% with $Item %>
                            $QuantityField
                        <% end_with %>
                    <% else %>
                        <a href="$Item.addLink" title="<%t SilverShop\Page\Product.AddToCartTitle "Add &quot;{Title}&quot; to your cart" Title=$Title.XML %>">
                            <%t SilverShop\Page\Product.AddToCart "Add to Cart" %>
                        </a>
                    <% end_if %>

                <% end_if %>
                </td>
            </tr>
    <% end_loop %>
</table>
