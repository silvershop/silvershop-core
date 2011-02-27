<div class="productItem">
	<% if Image %>
		<a href="$Link" title="<% sprintf(_t("Product.READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>"><img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" /></a>
	<% else %>
		<a href="$Link" title="<% sprintf(_t("Product.READMORE"),$Title) %>" class="noimage">no image</a>
	<% end_if %>

	<h3 class="productTitle"><a href="$Link" title="<% sprintf(_t("Product.READMORE"),$Title) %>">$Title</a></h3>
	<% if Model %><p><strong><% _t("Product.AUTHOR","Author") %>:</strong> $Model.XML</p><% end_if %>
	<p>$Content.LimitWordCount(10) <a href="$Link" title="<% sprintf(_t("Product.READMORE"),$Title) %>"><% _t("Product.READMORECONTENT","read more") %></a></p>
	<div>
		<% if Price != 0 %><span class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</span><% end_if %>
		<% if canPurchase %>
			<% if IsInCart %>
				<% control OrderItem %>
					<div class="quantityBox">
						<ul class="productActions">
							<li><a href="$RemoveAllLink" title="<% sprintf(_t("Product.REMOVE","Remove &quot;%s&quot; from your cart"),$Title) %>"><% _t("Product.REMOVELINK","Remove from cart") %></a></li>
						</ul>
					</div>
				<% end_control %>
			<% else %>
				<% if Variations %>
					<p class="addlink"><a href="$Link" title="<% sprintf(_t("Product.ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("Product.ADDVARIATIONSLINK","View variations") %></a></p>
				<% else %>
					<p class="addlink"><a href="$addLink" title="<% sprintf(_t("Product.ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("Product.ADDLINK","Add to cart") %></a></p>
				<% end_if %>
			<% end_if %>
		<% end_if %>
	</div>
</div>
