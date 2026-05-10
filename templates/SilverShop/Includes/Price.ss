<% if $PriceRange %>
	<div class="silvershop-price silvershop-price--range" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">
		<meta itemprop="priceCurrency" content="$Price.Currency" />
		<meta itemprop="lowPrice" content="$PriceRange.Min.Amount" />
		<meta itemprop="highPrice" content="$PriceRange.Max.Amount" />
		<strong class="silvershop-price__value silvershop-price__value--min">$PriceRange.Min.Nice</strong>
		<% if $PriceRange.HasRange %>
			<span class="silvershop-price__separator">-</span>
			<strong class="silvershop-price__value silvershop-price__value--max">$PriceRange.Max.Nice</strong>
		<% end_if %>
		<span class="silvershop-price__currency">$Price.Currency</span>
	</div>
<% else_if $Price %>
	<div class="silvershop-price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="priceCurrency" content="$Price.Currency" />
		<meta itemprop="price" content="$Price.Amount" />
		<strong class="silvershop-price__value">$Price.Nice</strong> <span class="silvershop-price__currency">$Price.Currency</span>
	</div>
<% end_if %>
