<div id="Sidebar">
	<% include Sidebar_Cart %>
	<% include Sidebar_Products %>
</div>
<div id="Product">
	<h1 class="pageTitle">$Title</h1>
	<div class="productDetails">
		<div class="productImage">
<% if Image.ContentImage %>
			<img class="realImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" />
<% else %>
			<img class="noImage" src="/ecommerce/images/productPlaceHolderThumbnail.gif" alt="<% _t("Product.NOIMAGEAVAILABLE","no image available") %>">
<% end_if %>
		</div>
<% if Variations %>
		<div class="variationsTable"><% include VariationsTable %></div>
<% else %>
	<% if canPurchase %>
		<p class="priceDisplay">$Price.Nice $Currency $TaxInfo.PriceSuffix</p>
		<ul class="productActions">
		<% if IsInCart %>
			<% control OrderItem %>
			<li><a href="$RemoveAllLink"><% _t("Product.REMOVELINK","&raquo; Remove from cart") %></a></li>
			<li><a href="$CheckoutLink"><% _t("Product.GOTOCHECKOUTLINK","&raquo; Go to the checkout") %></a></li>
			<% end_control %>
		<% else %>
			<li><a href="$addLink"><% _t("Product.ADDLINK","Add to cart") %></a></li>
		<% end_if %>
		</ul>
	<% end_if %>
<% end_if %>
	</div>
<% if Content %><div id="ContentHolder">$Content</div><% end_if %>
</div>
