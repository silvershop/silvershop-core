<% if Cart %>
	<% control Cart %>
	<div id="ShoppingCart">
		<h3 id="CartHeader"><% _t("Cart.HEADLINE","My Cart") %></h3>
		<table id="InformationTable" class="editable" cellspacing="0" cellpadding="0" summary="<% _t("TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
			<tbody>
				<% if Items %>
					<% control Items %>
						<% if ShowInTable %>
							<tr id="$TableID" class="$Classes hideOnZeroItems">
								<td <% if Link %><% else %> id="$TableTitleID"<% end_if %> class="product title" scope="row">
									<% if Link %>
										<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("Order_Content_Editable.ss.READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
									<% else %>
										$TableTitle
									<% end_if %>
								</td>
								<td class="center quantity">
									$QuantityField
								</td>
								<td class="right total" id="$TableTotalID">$Total.Nice</td>
							</tr>
						<% end_if %>
					<% end_control %>

					<tr class="gap summary hideOnZeroItems">
						<td colspan="2" scope="row"><% _t("SUBTOTAL","Sub-total") %></td>
						<td class="right" id="$TableSubTotalID">$SubTotal.Nice</td>
					</tr>

					<% if Modifiers %>
					<% control Modifiers %>
						<% if ShowInTable %>
							<tr id="$TableID" class="$Classes hideOnZeroItems">
								<td<% if Link %><% else %> id="$TableTitleID"<% end_if %> colspan="2" scope="row">
									<% if Link %>
										<a id="$TableTitleID" href="$Link" title="<% sprintf(_t("Order_Content_Editable.ss.READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
									<% else %>
										$TableTitle
									<% end_if %>
									$Form
								</td>
								<td class="right total" id="$TableTotalID">$TableValue.Nice</td>
							</tr>
						<% end_if %>
					<% end_control %>
					<% end_if %>

					<tr class="gap total summary hideOnZeroItems">
						<td colspan="2" scope="row"><% _t("TOTAL","Total") %></td>
						<td class="right total" id="$TableTotalID">$Total.Nice $Currency</td>
					</tr>
					<tr class="showOnZeroItems"<% if Items %> style="display: none"<% end_if %>>
						<td colspan="3" scope="row" class="center"><% _t("NOITEMS","There are <strong>no</strong> items in your cart.") %></td>
					</tr>
			</tbody>
		</table>

		<% else %>
			<p class="noItems"><% _t("Cart.NOITEMS","There are no items in your cart") %>.</p>
		<% end_if %>
	</div>
	<% end_control %>
<% end_if %>
