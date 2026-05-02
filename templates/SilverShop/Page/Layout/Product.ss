<div id="Product" class="silvershop-typography">
    <h1 class="silvershop-pageTitle">$Title</h1>
    <div class="silvershop-breadcrumbs">$Breadcrumbs</div>
    <div class="silvershop-productDetails">
        <% if $Image.ContentImage %>
            <img class="silvershop-productImage" src="$Image.ContentImage.URL" alt="<%t SilverShop\Page\Product.ImageAltText "{Title} image" Title=$Title %>" />
        <% else %>
            <div class="silvershop-noimage"><%t SilverShop\Page\Product.NoImage "no image" %></div>
        <% end_if %>
        <% if $InternalItemID %>
            <p class="silvershop-InternalItemID">
                <span class="silvershop-title"><%t SilverShop\Page\Product.Code "Product Code" %>:</span>
                <span class="silvershop-value">{$InternalItemID}</span>
            </p>
        <% end_if %>
        <% if $Model %>
            <p class="silvershop-Model">
                <span class="silvershop-title"><%t SilverShop\Page\Product.Model "Model" %>:</span>
                <span class="silvershop-value">$Model.XML</span>
            </p>
        <% end_if %>
        <% if $Size %>
            <p class="silvershop-Size">
                <span class="silvershop-title"><%t SilverShop\Page\Product.Size "Size" %>:</span>
                <span class="silvershop-value">$Size.XML</span>
            </p>
        <% end_if %>
        <% include SilverShop\Includes\Price %>
        <% if $IsInCart %>
            <p class="silvershop-NumItemsInCart">
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
        <div class="silvershop-productContent silvershop-typography">
            $Content
        </div>
    <% end_if %>
</div>
<% include SilverShop\Includes\SideBar %>

