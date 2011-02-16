<% if Cart %>
	<% control Cart %>
	<div id="ShoppingCart">
		<h3><% _t("Cart.HEADLINE","My Cart") %></h3>
		<% if Items %>
			<ul>
				<% control Items %>
					<% if ShowInCart %>
						<li id="$CartID" class="$Classes">
							<span class="itemdetails">
								<span<% if Link %><% else %> id="$CartTitleID"<% end_if %> class="title">
									<% if Link %>
										<a id="$CartTitleID" href="$Link" title="<% sprintf(_t("Cart.READMORE","Click here to read more on &quot;%s&quot;"),$CartTitle.XML) %>">$CartTitle.XML</a>
									<% else %>
										$CartTitle.XML
									<% end_if %>
								</span>
								<span class="price">
									<% _t("Cart.PRICE","Price") %> : <strong>$UnitPrice.Nice</strong>
								</span>
								<span class="quantity">
									<% _t("Cart.QUANTITY","Quantity") %> : $QuantityField
								</span>
								<span class="remove">
									<a class="ajaxQuantityLink" href="$removeallLink" title="<% sprintf(_t("Cart.REMOVEALL","Remove all of &quot;%s&quot; from your cart"),$CartTitle.XML) %>">
										<img src="ecommerce/images/remove.gif" alt="x"/>
									</a>
								</span>
							</span>
							<div class="clear"><!-- --></div>
						</li>
					<% end_if %>
				<% end_control %>
				<li class="subtotal"><% _t("Cart.SUBTOTAL","Subtotal") %>: <strong id="$CartSubTotalID">$SubTotal.Nice</strong></li>

				<% if Modifiers %>
				<% control Modifiers %>
					<% if ShowInCart %>
						<li id="$CartID" class="$Classes">
							<span><% if Link %><% else %> id="$CartTitleID"<% end_if %> class="title">
								<% if Link %>
									<a id="$CartTitleID" href="$Link" title="<% sprintf(_t("Cart.READMORE","Click here to read more on &quot;%s&quot;"),$CartTitle) %>">$CartTitle</a>
								<% else %>
									$CartTitle
								<% end_if %>
							</span>
							<span id="$CartTotalID">$CartValue</span>
							<span class="remove">
								<% if CanRemove %>
									<strong>
										<a class="ajaxQuantityLink" href="$RemoveLink" title="<% sprintf(_t("Cart.REMOVE","Remove &quot;%s&quot; from your order"),$TableTitle) %>">
											<img src="ecommerce/images/remove.gif" alt="x"/>
										</a>
									</strong>
								<% end_if %>
							</span>
						</li>
					<% end_if %>
				<% end_control %>
				<% end_if %>

				<li class="total"><% _t("Cart.TOTAL","Total") %>: <strong id="$CartTotalID">$Total.Nice $Currency</strong></li>
				<li class="buyProducts"><p><a class="checkoutButton" href="$checkoutLink" title="<% _t("Cart.CHECKOUTCLICK","Click here to go to the checkout") %>"><% _t("Cart.CHECKOUTGOTO","Go to checkout") %></a></p></li>
			</ul>
		<% else %>
			<p class="noItems"><% _t("Cart.NOITEMS","There are no items in your cart") %>.</p>
		<% end_if %>
	</div>
	<% end_control %>
<% end_if %>
