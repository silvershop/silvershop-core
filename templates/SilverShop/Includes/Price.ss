<% if $PriceRange %>
	<div class="silvershop-price">
		<strong class="silvershop-value">$PriceRange.Min.Nice</strong>
		<% if $PriceRange.HasRange %>
			- <strong class="silvershop-value">$PriceRange.Max.Nice</strong>
		<% end_if %>
		<span class="silvershop-currency">$Price.Currency</span>
	</div>
<% else_if $Price %>
	<div class="silvershop-price">
		<strong class="silvershop-value">$Price.Nice</strong> <span class="silvershop-currency">$Price.Currency</span>
	</div>
<% end_if %>