<li class="silvershop-product-card">
    <% if $Image %>
        <a class="silvershop-product-card__image-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
            <img class="silvershop-product-card__image" src="$Image.getThumbnail.URL" alt="<%t SilverShop\Page\Product.ImageAltText "{Title} image" Title=$Title %>" />
        </a>
    <% else %>
        <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>" class="silvershop-product-card__no-image"><!-- no image --></a>
    <% end_if %>
    <h3 class="silvershop-product-card__title"><a class="silvershop-product-card__title-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">$Title</a></h3>
    <% if $Model %><p class="silvershop-product-card__model"><strong><%t SilverShop\Page\Product.Model "Model" %>:</strong> $Model.XML</p><% end_if %>
    <div class="silvershop-product-card__actions">
        <% include SilverShop\Includes\Price %>
        <% if $View %>
            <div class="silvershop-product-card__view">
                <a class="silvershop-product-card__view-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
                    <%t SilverShop\Page\Product.View "View Product" %>
                </a>
            </div>
        <% else %>
            <% if $canPurchase %>
            <div class="silvershop-product-card__add">
                <a class="silvershop-product-card__add-link" href="$addLink" title="<%t SilverShop\Page\Product.AddToCartTitle "Add &quot;{Title}&quot; to your cart" Title=$Title %>">
                    <%t SilverShop\Page\Product.AddToCart "Add to Cart" %>
                    <% if $IsInCart %>
                        ($Item.Quantity)
                    <% end_if %>
                </a>
            </div>
            <% end_if %>
        <% end_if %>
    </div>
</li>
