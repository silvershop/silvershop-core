<% require themedCSS(sidecart,shop) %>
<div class="sidecart">
	<h3><%t ShoppingCart.Headline "Shopping cart" %></h3>
	<% if $Cart %>
		<% with $Cart %>
			<p class="itemcount">
				<% if $Items.Plural %>
					<%t ShoppingCart.ItemsInCartPlural 'There are <a href="{link}">{quantity} items</a> in your cart.' link=$Top.CartLink quantity=$Items.Quantity %>
				<% else %>
					<%t ShoppingCart.ItemsInCartSingular 'There is <a href="{link}">1 item</a> in your cart.' link=$Top.CartLink %>
				<% end_if %>
			</p>
			<div class="checkout">
				<a href="$Top.CheckoutLink"><%t ShoppingCart.Checkout "Checkout" %></a>
			</div>
			<% loop $Items %>
				<div class="item $EvenOdd $FirstLast">
					<% if $Product.Image %>
						<div class="image">
							<a href="$Product.Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
								<% with $Product %>$Image.setWidth(45)<% end_with %>
							</a>
						</div>
					<% end_if %>
					<p class="title">
						<a href="$Product.Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Title %>">
							$TableTitle
						</a>
					</p>
					<p class="quantityprice"><span class="quantity">$Quantity</span> <span class="times">x</span> <span class="unitprice">$UnitPrice.Nice</span></p>
					<% if $SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
					<a class="remove" href="$removeallLink" title="<%t ShoppingCart.RemoveTitle "Remove &quot;{Title}&quot; from your cart." Title=$TableTitle %>">x</a>
				</div>
			<% end_loop %>
		<% end_with %>
	<% else %>
		<p class="noItems"><%t ShoppingCart.NoItems "There are no items in your cart." %></p>
	<% end_if %>
</div>
