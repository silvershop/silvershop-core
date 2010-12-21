<h3 class="orderInfo"><% _t("ORDERINFORMATION","Order Information") %></h3>
<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="<% _t("TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
	<thead>
		<tr>
			<th scope="col" class="left"><% _t("PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("PRICE","Price") %> ($Currency)</th>
			<th scope="col" class="right"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
			<th scope="col" class="right"></th>
		</tr>
	</thead>
	<tbody>
		<% if Items %>
			<% control Items %>
				<% if ShowInTable %>
					<tr id="$TableID" class="$Classes">
						<td<% if Link %><% else %> id="$TableTitleID"<% end_if %> class="product title" scope="row">
							<% if Link %>
								<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
							<% else %>
								$TableTitle
							<% end_if %>
						</td>
						<td class="center quantity">
							$QuantityField
						</td>
						<td class="right unitprice">$UnitPrice.Nice</td>
						<td class="right total" id="$TableTotalID">$Total.Nice</td>
						<td class="right remove">
							<strong>
								<a class="ajaxQuantityLink" href="$removeallLink" title="<% sprintf(_t("REMOVEALL","Remove all of &quot;%s&quot; from your cart"),$TableTitle) %>">
									<img src="ecommerce/images/remove.gif" alt="x"/>
								</a>
							</strong>
						</td>
					</tr>
				<% end_if %>
			<% end_control %>

			<tr class="gap summary">
				<td colspan="2" scope="row"><% _t("SUBTOTAL","Sub-total") %></td>
				<td>&nbsp;</td>
				<td class="right" id="$TableSubTotalID">$SubTotal.Nice</td>
				<td>&nbsp;</td>
			</tr>

			<% if Modifiers %>
			<% control Modifiers %>
				<% if ShowInTable %>
					<tr id="$TableID" class="$Classes">
						<td<% if Link %><% else %> id="$TableTitleID"<% end_if %> colspan="2" scope="row">
							<% if Link %>
								<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
							<% else %>
								$TableTitle
							<% end_if %>
							$Form
						</td>
						<td>&nbsp;</td>
						<td class="right" id="$TableTotalID">$TableValue.Nice</td>
						<td class="right remove">
							<% if CanRemove %>
								<strong>
									<a class="ajaxQuantityLink" href="$removeLink" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your order"),$TableTitle) %>">
										<img src="ecommerce/images/remove.gif" alt="x"/>
									</a>
								</strong>
							<% end_if %>
						</td>
					</tr>
				<% end_if %>
			<% end_control %>
			<% end_if %>

			<tr class="gap Total">
				<td colspan="2" scope="row"><% _t("TOTAL","Total") %></td>
				<td>&nbsp;</td>
				<td class="right" id="$TableTotalID">$Total.Nice $Currency</td>
				<td>&nbsp;</td>
			</tr>
		<% else %>
			<tr>
				<td colspan="5" scope="row" class="center"><% _t("NOITEMS","There are <strong>no</strong> items in your cart.") %></td>
			</tr>
		<% end_if %>
	</tbody>
</table>
