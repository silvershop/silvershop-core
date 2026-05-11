<% if $Top.SilverShopIncludeDefaultStyles %>
<% require css("silvershop/core:client/dist/css/silvershop-base.css") %>
<% require css("silvershop/core:client/dist/css/sidecart.css") %>
<% end_if %>

<div class="silvershop-sidecart">
    <h3 class="silvershop-sidecart__heading"><%t SilverShop\Cart\ShoppingCart.Headline "Shopping cart" %></h3>
    <% if $Cart %>
        <% with $Cart %>
            <div class="silvershop-sidecart__summary">
                <p class="silvershop-sidecart__count">
                    <% if $Items.Plural %>
                        <%t SilverShop\Cart\ShoppingCart.ItemsInCartPlural 'There are <a href="{link}">{quantity} items</a> in your cart.' link=$Top.CartLink quantity=$Items.Quantity %>
                    <% else %>
                        <%t SilverShop\Cart\ShoppingCart.ItemsInCartSingular 'There is <a href="{link}">1 item</a> in your cart.' link=$Top.CartLink %>
                    <% end_if %>
                </p>
                <div class="silvershop-sidecart__checkout">
                    <a class="silvershop-sidecart__checkout-link" href="$Top.CheckoutLink"><%t SilverShop\Cart\ShoppingCart.Checkout "Checkout" %></a>
                </div>
            </div>
            <ul class="silvershop-sidecart__items">
                <% loop $Items %>
                    <li class="silvershop-sidecart__item $EvenOdd $FirstLast">
                        <div class="silvershop-sidecart__item-main">
                            <% if $Product.Image %>
                                <div class="silvershop-sidecart__image">
                                    <a class="silvershop-sidecart__image-link" href="$Product.Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
                                        <% with $Product %>$Image.ScaleWidth(45)<% end_with %>
                                    </a>
                                </div>
                            <% end_if %>
                            <div class="silvershop-sidecart__item-body">
                                <p class="silvershop-sidecart__title">
                                    <a class="silvershop-sidecart__title-link" href="$Product.Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
                                        $TableTitle
                                    </a>
                                </p>
                                <p class="silvershop-sidecart__quantityprice">
                                    <span class="silvershop-sidecart__quantity">$Quantity</span>
                                    <span class="silvershop-sidecart__times" aria-hidden="true">×</span>
                                    <span class="silvershop-sidecart__unit-price">$UnitPrice.Nice</span>
                                </p>
                                <% if $SubTitle %>
                                    <p class="silvershop-sidecart__subtitle">$SubTitle</p>
                                <% end_if %>
                            </div>
                        </div>
                        <a class="silvershop-sidecart__remove" href="$removeAllLink" title="<%t SilverShop\Cart\ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>" aria-label="<%t SilverShop\Cart\ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>">×</a>
                    </li>
                <% end_loop %>
            </ul>
        <% end_with %>
    <% else %>
        <p class="silvershop-sidecart__empty"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
</div>
