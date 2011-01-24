<table id="AddressesTable" class="infotable">
	<tr>
		<th scope="col"><% _t("PURCHASEDBY","Purchased by") %></th>
		<th scope="col"><% _t("SHIPTO","Ship To") %></th>
	</tr>
	<tr>
		<td><% include Order_AddressBilling %></td>
		<td><% include Order_AddressShipping %></td>
	</tr>
</table>
