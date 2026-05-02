<div class="silvershop-pricetag">
    <% if $DiscountedPrice %>
        <span class="silvershop-original silvershop-strikeout">
            <span class="silvershop-symbol">$Price.Symbol</span>
            <strong class="silvershop-main">$Price.Main</strong>
            <small class="silvershop-fractional">$Price.Fractional</small>
            <span class="silvershop-code">$Price.CurrencyCode</span>
        </span>
        <span class="silvershop-discounted">$DiscountedPrice.Nice</span> <%t SilverShop\Includes\PriceTag.SAVE "Save" %>: <span class="silvershop-savings">$DiscountedPrice.Savings</span>
    <% else %>
        <span class="silvershop-original"><strong class="silvershop-price">$Price.Nice</strong></span>
    <% end_if %>
    <% if $RecommendedPrice %><span>$RecommendedPrice.Nice</span><% end_if %>
</div>
