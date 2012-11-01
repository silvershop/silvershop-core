<% require themedCSS(cart) %>
<table class="cart table" summary="<% _t("TABLESUMMARY","Current contents of your cart.") %>">
	<colgroup class="image"/>
	<colgroup class="product title"/>
	<colgroup class="unitprice" />
	<colgroup class="quantity" />
	<colgroup class="total"/>
	<thead>
		<tr>
			<th scope="col"></th>
			<th scope="col"><% _t("PRODUCT","Product") %></th>
			<th scope="col"><% _t("UNITPRICE","Unit Price") %></th>
			<th scope="col"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% control Items %>
			<% include Order_ItemLine %>
		<% end_control %>
	</tbody>
	<tfoot>
		<tr class="subtotal">
			<th colspan="4" scope="row"><% _t("SUBTOTAL","Sub-total") %></th>
			<td id="$TableSubTotalID">$SubTotal.Nice</td>
		</tr>
	</tfoot>
</table>