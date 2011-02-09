<% control Parent %>
	<% include ProductMenu %>
<% end_control %>

<div id="Product">
	<h1 class="pageTitle">$Title</h1>

	<div class="productDetails">

		<% if Image.ContentImage %>
			<img class="productImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" />
		<% else %>
			<div class="noimage"><% sprintf(_t("Product.NOIMAGE","no image"),$Title) %></div>
		<% end_if %>

		<p><% _t("Product.ITEMID","Item #") %>{$ID}</p>
		<% if Model %><p><% _t("Product.MODEL","Model") %>: $Model.XML</p><% end_if %>
		<% if Size %><p><% _t("Product.SIZE","Size") %>: $Size.XML</p><% end_if %>
		<% if Variations %>
			<div class="quantityBox">
				<% include VariationsTable %>
			</div>
		<% else %>
			<% if Price != 0 %><p class="priceDisplay">$Price.Nice $Currency $TaxInfo.PriceSuffix</p><% end_if %>
			<% if canPurchase %>
				<% if IsInCart %>
					<% control OrderItem %>
						<div class="quantityBox">
							<span><% _t("Product.QUANTITYCART","Quantity in cart") %>:</span>
							$QuantityField
							<ul class="productActions">
								<li><a href="$RemoveAllLink" title="<% sprintf(_t("Product.REMOVE","Remove &quot;%s&quot; from your cart"),$BuyableTitle) %>"><% _t("Product.REMOVELINK","&raquo; Remove from cart") %></a></li>
								<li><a href="$checkoutLink" title="<% _t("Product.GOTOCHECKOUT","Go to the checkout now") %>"><% _t("Product.GOTOCHECKOUTLINK","&raquo; Go to the checkout") %></a></li>
							</ul>
						</div>
					<% end_control %>
				<% else %>
					<p class="quantityBox"><a href="$addLink" title="<% sprintf(_t("Product.ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("Product.ADDLINK","Add this item to cart") %></a></p>
				<% end_if %>
			<% end_if %>
		<% end_if %>
	</div>
	<% if Content %>
		<div id="ContentHolder">$Content</div>
	<% end_if %>
</div>
