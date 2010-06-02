<table cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th scope="col" class="title"><% _t("PRODUCT","Product") %></th>
			<th scope="col" class="quantity"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col" class="price"><% _t("PRICE","Price") %> ($Currency)</th>
			<th scope="col" class="price"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% if Items %>
			<% control Items %>
				<% if ShowInTable %>
					<tr id="$TableID" class="$Classes">
						<td<% if Link %><% else %> id="$TableTitleID"<% end_if %> scope="row" class="title">
							<% if Link %>
								<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
							<% else %>
								$TableTitle
							<% end_if %>
						</td>
						<td class="quantity">$Quantity</td>
						<td class="price">$UnitPrice.Nice</td>
						<td id="$TableTotalID" class="price">$Total.Nice</td>
					</tr>
				<% end_if %>
			<% end_control %>
			
			<tr class="othertotal">
				<td colspan="3" scope="row" class="title"><% _t("SUBTOTAL","Sub-total") %></td>
				<td id="$TableSubTotalID" class="price">$SubTotal.Nice</td>
			</tr>
			
			<% control Modifiers %>
				<% if ShowInTable %>
					<tr id="$TableID" class="$Classes">
						<td<% if Link %><% else %> id="$TableTitleID"<% end_if %> colspan="3" scope="row" class="title">
							<% if Link %>
								<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
							<% else %>
								$TableTitle
							<% end_if %>
						</td>
						<td id="$TableTotalID" class="price"><% if IsChargable %>$Amount.Nice<% else %>-$Amount.Nice<% end_if %></td>
					</tr>
				<% end_if %>
			<% end_control %>
			
			<tr class="total">
				<td colspan="3" scope="row" class="title"><% _t("TOTAL","Total") %></td>
				<td id="$TableTotalID" class="price">$Total.Nice $Currency</td>
			</tr>
			
			<tr><td colspan="4" class="transparent"></td></tr>
			
			<tr class="othertotal">
				<td class="transparent"></td>
				<td colspan="2" scope="row" class="title"><% _t("TOTALOUTSTANDING","Total outstanding") %></td>
				<td class="price">$TotalOutstanding.Nice $Currency</td>
			</tr>
		<% else %>
			<tr>
				<td colspan="4" scope="row" class="title"><% _t("NOITEMS","There are <strong>no</strong> items in your order.") %></td>
			</tr>
		<% end_if %>
	</tbody>
</table>