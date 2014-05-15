<% require themedCSS(sidecart) %>
<div class="sidecart">
	<h3><% _t("HEADLINE","My Cart") %></h3>
	<% if Cart %>
		<% with Cart %>
			<p class="itemcount">There <% if Items.Plural %>are<% else %>is<% end_if %> <a href="$Top.CartLink">$Items.Quantity item<% if Items.Plural %>s<% end_if %></a> in your cart.</p>
			<div class="checkout">
				<a href="$Top.CheckoutLink">Checkout</a>
			</div>
			<% loop Items %>
				<div class="item $EvenOdd $FirstLast">
					<% if Product.Image %>
						<div class="image">
							<a href="$Product.Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
								<% with Product %>$Image.setWidth(45)<% end_with %>
							</a>
						</div>
					<% end_if %>
					<p class="title">
						<a href="$Product.Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
							$TableTitle
						</a>
					</p>
					<p class="quantityprice"><span class="quantity">$Quantity</span> <span class="times">x</span> <span class="unitprice">$UnitPrice.Nice</span></p>
					<% if SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
					<a class="remove" href="$removeallLink" title="<% sprintf(_t("REMOVEALL","remove from cart"),$TableTitle) %>">
						<img src="shop/images/remove.gif" alt="x"/>
					</a>
				</div>
			<% end_loop %>
		<% end_with %>
	<% else %>
		<p class="noItems"><% _t("NOITEMS","There are no items in your cart") %>.</p>
	<% end_if %>
</div>