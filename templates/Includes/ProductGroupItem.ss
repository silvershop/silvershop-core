<li class="productItem">
	<% if Image %>
		<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>"><img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" /></a>
	<% else %>
		<a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>"><img src="ecommerce/images/productPlaceHolderThumbnail.gif" alt="<% sprintf(_t("NOIMAGE","Sorry, no product image for &quot;%s&quot;"),$Title) %>" /></a>
	<% end_if %>

	<h3 class="productTitle"><a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>">$Title</a></h3>
	<% if Model %><p><strong><% _t("AUTHOR","Author") %>:</strong> $Model.XML</p><% end_if %>
	<p>$Content.LimitWordCount(15) <a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>"><% _t("READMORECONTENT","Click to read more &raquo;") %></a></p>
	<div>
		<% if Price != 0 %><span class="price_display">$Price.Nice $Currency $TaxInfo.PriceSuffix</span><% end_if %>
		<% if AllowPurchase %>
			<% if IsInCart %>
				<% control Item %>
					<div class="quantityBox">
						<span><% _t("QUANTITYCART","Quantity in cart") %>:</span>
						<a class="ajaxQuantityLink" href="$removeLink" title="<% sprintf(_t("REMOVEALL","Remove one of &quot;%s&quot; from your cart"),$Title) %>">
							<img src="ecommerce/images/minus.gif" alt="-" />
						</a>	
						$AjaxQuantityField
						<a class="ajaxQuantityLink" href="$addLink" title="<% sprintf(_t("ADDONE","Add one more of &quot;%s&quot; to your cart"),$Title) %>">
							<img src="ecommerce/images/plus.gif" alt="+" />
						</a>
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
	</div>
</li>																			
