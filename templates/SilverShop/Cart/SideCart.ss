<% require css("silvershop/core: client/dist/css/sidecart.css") %>

<div class="silvershop-sidecart">
    <h3 class="silvershop-sidecart__heading"><%t SilverShop\Cart\ShoppingCart.Headline "Shopping cart" %></h3>
    <% if $Cart %>
        <% with $Cart %>
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
            <% loop $Items %>
                <div class="silvershop-sidecart__item $EvenOdd $FirstLast">
                    <% if $Product.Image %>
                        <div class="silvershop-sidecart__image">
                            <a class="silvershop-sidecart__image-link" href="$Product.Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
                                <% with $Product %>$Image.ScaleWidth(45)<% end_with %>
                            </a>
                        </div>
                    <% end_if %>
                    <p class="silvershop-sidecart__title">
                        <a class="silvershop-sidecart__title-link" href="$Product.Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
                            $TableTitle
                        </a>
                    </p>
                    <p class="silvershop-sidecart__quantityprice"><span class="silvershop-sidecart__quantity">$Quantity</span> <span class="silvershop-sidecart__times">x</span> <span class="silvershop-sidecart__unit-price">$UnitPrice.Nice</span></p>
                    <% if $SubTitle %><p class="silvershop-sidecart__subtitle">$SubTitle</p><% end_if %>
                    <a class="silvershop-sidecart__remove" href="$removeAllLink" title="<%t SilverShop\Cart\ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>">x</a>
                </div>
            <% end_loop %>
        <% end_with %>
    <% else %>
        <p class="silvershop-sidecart__empty"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
</div>
