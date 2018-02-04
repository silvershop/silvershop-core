<table class="variationstable">
    <tr>
        <th><%t ProductVariation.SINGULARNAME "Variation" %></th>
        <th><%t Product.Price "Price" %></th>
        <% if $canPurchase %>
            <th><%t Order.Quantity "Quantity" %></th>
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
                        <a href="$Item.addLink" title="<%t Product.AddToCartTitle "Add &quot;{Title}&quot; to your cart" Title=$Title.XML %>">
                            <%t Product.AddToCart "Add to Cart" %>
                        </a>
                    <% end_if %>

                <% end_if %>
                </td>
            </tr>
    <% end_loop %>
</table>
