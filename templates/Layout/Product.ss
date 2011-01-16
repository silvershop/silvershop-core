<% control Parent %>
	<% include ProductMenu %>
<% end_control %>

<div id="Product">
	<h1 class="pageTitle">$Title</h1>

	<div class="productDetails">

		<% if Image.ContentImage %>
			<img class="productImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("Product.ss.IMAGE","%s image"),$Title) %>" />
		<% else %>
			<div class="noimage">no image</div>
		<% end_if %>

		<p><% _t("ITEMID","Item #") %>{$ID}</p>
		<% if Model %><p><% _t("MODEL","Model") %>: $Model.XML</p><% end_if %>
		<% if Size %><p><% _t("SIZE","Size") %>: $Size.XML</p><% end_if %>
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
							<span><% _t("QUANTITYCART","Quantity in cart") %>:</span>
							$QuantityField
							<ul class="productActions">
								<li><a href="$removeallLink" title="<% sprintf(_t("Product.ss.REMOVE","Remove &quot;%s&quot; from your cart"),$BuyableTitle) %>"><% _t("REMOVELINK","&raquo; Remove from cart") %></a></li>
								<li><a href="$checkoutLink" title="<% _t("Product.ss.GOTOCHECKOUT","Go to the checkout now") %>"><% _t("GOTOCHECKOUTLINK","&raquo; Go to the checkout") %></a></li>
							</ul>
						</div>
					<% end_control %>
				<% else %>
					<p class="quantityBox"><a href="$addLink" title="<% sprintf(_t("Product.ss.ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("ADDLINK","Add this item to cart") %></a></p>
				<% end_if %>
			<% end_if %>
		<% end_if %>
	</div>
	<% if Content %>
		<div id="ContentHolder">$Content</div>
	<% end_if %>
</div>
