<% control Parent %>
	<% include ProductMenu %>
<% end_control %>

<div id="Product">
	<h1 class="pageTitle">$Title</h1>

	<div class="productDetails">

		<% if Image.ContentImage %>
			<img class="productImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" />
		<% else %>
			<div class="noimage">no image</div>
		<% end_if %>

		<p><% _t("ItemID","Item #") %>{$ID}</p>
		<% if Model %><p><% _t("MODEL","Model") %>: $Model.XML</p><% end_if %>
		<% if Size %><p><% _t("SIZE","Size") %>: $Size.XML</p><% end_if %>
		<% if Variations %>
			<div class="quantityBox">
				<table class="quantityTable">
					<tr>
						<th>Variation</th><th>Price</th><% if canPurchase %><th><% _t("QUANTITYCART","Quantity in cart") %></th><% end_if %>
					</tr>
					<% control Variations %>
							<tr>
								<td>$Title.XML</td>
								<td>$Price.Nice $Currency $TaxInfo.PriceSuffix</td>
								<td>
								<% if canPurchase %>
									<% if IsInCart %>
										<% control Item %>
											<a class="ajaxQuantityLink" href="$removeLink" title="<% sprintf(_t("REMOVEALL","Remove one of &quot;%s&quot; from your cart"),$Title.XML) %>">
												<img src="ecommerce/images/minus.gif" alt="-" />
											</a>
											$AjaxQuantityField
											<a class="ajaxQuantityLink" href="$addLink" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Title.XML) %>">
												<img src="ecommerce/images/plus.gif" alt="+" />
											</a>
										<% end_control %>
									<% else %>
										<a href="$Item.addLink" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title.XML) %>"><% _t("ADDLINK","Add this item to cart") %></a>
									<% end_if %>

								<% end_if %>
								</td>
							</tr>
					<% end_control %>
				</table>
			</div>
		<% else %>
			<% if Price != 0 %><p class="priceDisplay">$Price.Nice $Currency $TaxInfo.PriceSuffix</p><% end_if %>
			<% if canPurchase %>
				<% if IsInCart %>
					<% control Item %>
						<div class="quantityBox">
							<span><% _t("QUANTITYCART","Quantity in cart") %>:</span>
							$QuantityField
							<ul class="productActions">
								<li><a href="$removeallLink" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your cart"),$Title) %>"><% _t("REMOVELINK","&raquo; Remove from cart") %></a></li>
								<li><a href="$checkoutLink" title="<% _t("GOTOCHECKOUT","Go to the checkout now") %>"><% _t("GOTOCHECKOUTLINK","&raquo; Go to the checkout") %></a></li>
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
