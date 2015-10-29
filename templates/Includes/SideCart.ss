<% require themedCSS(sidecart,shop) %>
<div class="sidecart">
	<h3><% _t("HEADLINE","My Cart") %></h3>
	<% if $Cart %>
		<% with $Cart %>
			<p class="itemcount">
				<% if $Items.Plural %>
					<%t ShoppingCart.ITEMS_IN_CART_PLURAL 'There are <a href="{link}">{quantity} items</a> in your cart.' link=$Top.CartLink quantity=$Items.Quantity %>
				<% else %>
					<%t ShoppingCart.ITEMS_IN_CART_SINGULAR 'There is <a href="{link}">1 item</a> in your cart.' link=$Top.CartLink %>
				<% end_if %>
			</p>
			<div class="checkout">
				<a href="$Top.CheckoutLink"><%t ShoppingCart.CHECKOUT "Checkout" %></a>
			</div>
			<% loop $Items %>
				<div class="item $EvenOdd $FirstLast">
					<% if $Product.Image %>
						<div class="image">
							<a href="$Product.Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
								<% with $Product %>$Image.setWidth(45)<% end_with %>
							</a>
						</div>
					<% end_if %>
					<p class="title">
						<a href="$Product.Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
							$TableTitle
						</a>
					</p>
					<p class="quantityprice"><span class="quantity">$Quantity</span> <span class="times">x</span> <span class="unitprice">$UnitPrice.Nice</span></p>
					<% if $SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
					<a class="remove" href="$removeallLink" title="<% sprintf(_t("REMOVEALL","remove from cart"),$TableTitle) %>">x</a>
				</div>
			<% end_loop %>
		<% end_with %>
	<% else %>
		<p class="noItems"><% _t("NOITEMS","There are no items in your cart") %>.</p>
	<% end_if %>
</div>
