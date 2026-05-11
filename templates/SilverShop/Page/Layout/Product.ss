<% if $SilverShopIncludeDefaultStyles %>
<% require css("silvershop/core:client/dist/css/silvershop-base.css") %>
<% require css("silvershop/core:client/dist/css/product.css") %>
<% end_if %>

<div class="silvershop-layout silvershop-layout--product silvershop-layout--with-sidebar">
    <div class="silvershop-layout__main">
        <div class="silvershop-product silvershop-typography" <% include SilverShop\Includes\SchemaOrg\ProductScope %>>
            <h1 class="silvershop-product__title" <% include SilverShop\Includes\SchemaOrg\ProductName %>>$Title</h1>
            <div class="silvershop-product__breadcrumbs">$Breadcrumbs</div>
            <div class="silvershop-product__details">
                <% if $Image.ContentImage %>
                    <img class="silvershop-product__image" <% include SilverShop\Includes\SchemaOrg\ProductImage %> src="$Image.ContentImage.URL" alt="<%t SilverShop\Page\Product.ImageAltText "{Title} image" Title=$Title %>" />
                <% else %>
                    <div class="silvershop-product__no-image"><%t SilverShop\Page\Product.NoImage "no image" %></div>
                <% end_if %>
                <% if $InternalItemID %>
                    <p class="silvershop-product__attribute silvershop-product__attribute--code">
                        <span class="silvershop-product__attribute-label"><%t SilverShop\Page\Product.Code "Product Code" %>:</span>
                        <span class="silvershop-product__attribute-value" <% include SilverShop\Includes\SchemaOrg\ProductSku %>>{$InternalItemID}</span>
                    </p>
                <% end_if %>
                <% if $Model %>
                    <p class="silvershop-product__attribute silvershop-product__attribute--model">
                        <span class="silvershop-product__attribute-label"><%t SilverShop\Page\Product.Model "Model" %>:</span>
                        <span class="silvershop-product__attribute-value">$Model.XML</span>
                    </p>
                <% end_if %>
                <% if $Size %>
                    <p class="silvershop-product__attribute silvershop-product__attribute--size">
                        <span class="silvershop-product__attribute-label"><%t SilverShop\Page\Product.Size "Size" %>:</span>
                        <span class="silvershop-product__attribute-value">$Size.XML</span>
                    </p>
                <% end_if %>
                <% include SilverShop\Includes\Price %>
                <% if $IsInCart %>
                    <p class="silvershop-product__cart-info">
                        <% if $Item.Quantity == 1 %>
                            <%t SilverShop\Page\Product.NumItemsInCartSingular "You have this item in your cart" %>
                        <% else %>
                            <%t SilverShop\Page\Product.NumItemsInCartPlural "You have {Quantity} items in your cart" Quantity=$Item.Quantity %>
                        <% end_if %>
                    </p>
                <% end_if %>
                $Form
            </div>
            <% if $Content %>
                <div class="silvershop-product__content silvershop-typography" <% include SilverShop\Includes\SchemaOrg\ProductDescription %>>
                    $Content
                </div>
            <% end_if %>
        </div>
    </div>
    <% include SilverShop\Includes\SideBar %>
</div>
