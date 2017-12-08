<div class="productItem">
    <% if $Image %>
        <a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
            <img src="$Image.Thumbnail.URL" alt="<%t Product.ImageAltText "{Title} image" Title=$Title %>" />
        </a>
    <% else %>
        <a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>" class="noimage"><!-- no image --></a>
    <% end_if %>
    <h3 class="productTitle"><a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">$Title</a></h3>
    <% if $Model %><p><strong><%t Product.Model "Model" %>:</strong> $Model.XML</p><% end_if %>
    <div>
        <% include Price %>
        <% if $View %>
            <div class="view">
                <a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
                    <%t Product.View "View Product" %>
                </a>
            </div>
        <% else %>
            <% if $canPurchase %>
            <div class="add">
                <a href="$addLink" title="<%t Product.AddToCartTitle "Add &quot;{Title}&quot; to your cart" Title=$Title %>">
                    <%t Product.AddToCart "Add to Cart" %>
                    <% if $IsInCart %>
                        ($Item.Quantity)
                    <% end_if %>
                </a>
            </div>
            <% end_if %>
        <% end_if %>
    </div>
</div>
