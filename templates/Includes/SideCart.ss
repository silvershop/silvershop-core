<% require themedCSS(sidecart) %>
<% if Cart %>
	<% control Cart %>
	<div class="sidecart">
		<h3><% _t("HEADLINE","My Cart") %></h3>
		<% if Items %>
			<p class="itemcount">There are <a href="$CartLink">$Items.Count items</a> in your cart.</p>
			<div class="checkout">
				<a href="$CheckoutLink">Checkout</a>
			</div>
			<% control Items %>
				<div class="item $EvenOdd $FirstLast">
					<% if Product.Image %>
						<div class="image">
							<a href="$Product.Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
								<% control Product %>$Image.setWidth(45)<% end_control %>
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
			<% end_control %>
		<% else %>
			<p class="noItems"><% _t("NOITEMS","There are no items in your cart") %>.</p>
		<% end_if %>
	</div>
	<% end_control %>
<% end_if %>