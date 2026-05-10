<div class="silvershop-pricetag">
    <% if $DiscountedPrice %>
        <span class="silvershop-pricetag__original silvershop-pricetag__original--strikeout">
            <span class="silvershop-pricetag__symbol">$Price.Symbol</span>
            <strong class="silvershop-pricetag__main">$Price.Main</strong>
            <small class="silvershop-pricetag__fractional">$Price.Fractional</small>
            <span class="silvershop-pricetag__code">$Price.CurrencyCode</span>
        </span>
        <span class="silvershop-pricetag__discounted">$DiscountedPrice.Nice</span>
        <span class="silvershop-pricetag__save-label"><%t SilverShop\Includes\PriceTag.SAVE "Save" %>:</span>
        <span class="silvershop-pricetag__savings">$DiscountedPrice.Savings</span>
    <% else %>
        <span class="silvershop-pricetag__original"><strong class="silvershop-pricetag__price">$Price.Nice</strong></span>
    <% end_if %>
    <% if $RecommendedPrice %><span class="silvershop-pricetag__recommended">$RecommendedPrice.Nice</span><% end_if %>
</div>
