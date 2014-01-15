<table class="ss-gridfield-table">
	<thead>
		<tr class="title">
			<th colspan="5">
				<h2><% _t("ITEMS","Items") %></h2>
			</th>
		</tr>
		<tr class="header">
			<th class="main"></th>
			<th class="main"><span class="ui-button-text"><% _t("PRODUCT","Product") %></span></th>
			<th class="main"><span class="ui-button-text"><% _t("UNITPRICE","Unit Price") %></span></th>
			<th class="main"><span class="ui-button-text"><% _t("QUANTITY", "Quantity") %></span></th>
			<th class="main"><span class="ui-button-text"><% _t("TOTALPRICE","Total Price") %> ($Currency)</span></th>
		</tr>
	</thead>
	<tbody>
		<% loop Items %>
			<% include OrderAdmin_Content_ItemLine %>
		<% end_loop %>
	</tbody>
	<% include OrderAdmin_Content_SubTotals %>
</table>