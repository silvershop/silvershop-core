<li class="productItem">
	<div class="productImage">
	<% if Image %>
		<a href="$Link"><img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" /></a>
	<% else %>
		<a href="$Link" class="noImage"><img src="$DefaultImageLink" alt="<% _t("Product.NOIMAGEAVAILABLE","no image available") %>"></a>
	<% end_if %>
	</div>
	<h3 class="productTitle"><a href="$Link">$Title</a></h3>
	<div class="addToCartSection">
		<% if Price != 0 %><span class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</span><% end_if %>
		<% if canPurchase %>
			<% if IsInCart %>
				<% control OrderItem %>
					<div class="quantityBox">
						<ul class="productActions">
							<li><a href="$RemoveAllLink"><% _t("Product.REMOVELINK","Remove from cart") %></a></li>
						</ul>
					</div>
				<% end_control %>
			<% else %>
				<% if Variations %>
					<p class="addlink"><a href="$Link"><% _t("Product.ADDVARIATIONSLINK","View variations") %></a></p>
				<% else %>
					<p class="addlink"><a href="$AddLink"><% _t("Product.ADDLINK","Add to cart") %></a></p>
				<% end_if %>
			<% end_if %>
		<% end_if %>
	</div>
</li>
