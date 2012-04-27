<% require themedCSS(product) %>
<div id="Sidebar">
	<% control Parent %>
		<% include ProductMenu %>
	<% end_control %>
	<div class="cart">
		<% include SideCart %>
	</div>
</div>
<div id="Product" class="typography">
	<h1 class="pageTitle">$Title</h1>
	<div class="breadcrumbs">$Breadcrumbs</div>
	<div class="productDetails">
		<% if Image.ContentImage %>
			<img class="productImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" />
		<% else %>
			<div class="noimage">no image</div>
		<% end_if %>
		<% if InternalItemID %><p><% _t("CODE","Product Code") %>: {$InternalItemID}</p><% end_if %>
		<% if Model %><p><% _t("MODEL","Model") %>: $Model.XML</p><% end_if %>
		<% if Size %><p><% _t("SIZE","Size") %>: $Size.XML</p><% end_if %>
		<% if Variations %>
			$VariationForm
		<% else %>
			<% if Price %>
				<div class="price">
					<strong class="value">$Price.Nice</strong> <span class="currency">$Currency</span>
				</div>
			<% end_if %>
			<% if canPurchase %>
				<% if IsInCart %>
					<% control Item %>
						<div class="quantityBox">
							<span><% _t("QUANTITYCART","Quantity in cart") %>:</span> 
							$QuantityField
							<ul class="productActions">
								<li><a href="$removeallLink" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your cart"),$Title) %>"><% _t("REMOVELINK","Remove from cart") %></a></li>
								<li><a href="$checkoutLink" title="<% _t("GOTOCHECKOUT","Go to the checkout now") %>"><% _t("GOTOCHECKOUTLINK","Go to the checkout") %></a></li>
							</ul>
						</div>
					<% end_control %>
				<% else %>
					<p class="quantityBox"><a href="$addLink" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title) %>"><% _t("ADDLINK","Add this item to cart") %></a></p>
				<% end_if %>
			<% end_if %>
		<% end_if %>
	</div>
	<% if Content %>
		<div class="productContent typography">
			$Content
		</div>
	<% end_if %>
</div>