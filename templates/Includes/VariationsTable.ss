<table class="variationstable">
	<tr>
		<th>Variation</th><th>Price</th><% if canPurchase %><th><% _t("QUANTITYCART","Quantity in cart") %></th><% end_if %>
	</tr>
	<% control Variations %>
			<tr>
				<td>$Title.XML</td>
				<td>$Price.Nice $Currency</td>
				<td>
				<% if canPurchase %>
					<% if IsInCart %>
						<% control Item %>
							$QuantityField
						<% end_control %>
					<% else %>
						<a href="$Item.addLink" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title.XML) %>"><% _t("ADDLINK","Add this item to cart") %></a>
					<% end_if %>

				<% end_if %>
				</td>
			</tr>
	<% end_control %>
</table>