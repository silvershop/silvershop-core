<div id="Account">
	<div class="typography">
		<h2>$Title</h2>
		<% if Content %>
			$Content
		<% end_if %>
	</div>
	<ul id="PastOrders">
		<li>
			<h3><% _t("HISTORY","Your Order History") %></h3>
		</li>
		<li>
			<h4><% _t("COMPLETED","Completed Orders") %></h4>
		</li>
		<% if CompleteOrders %>
			<% control CompleteOrders %>
				<li>
					<a href="$Link" title="<% sprintf(_t("READMORE","Read more on Order #%s"),$ID) %>"><% _t("ORDER","Order #") %>{$ID}</a> ($Created.Nice)
				</li>
			<% end_control %>
		<% else %>
			<li>
				<% _t("NOCOMPLETED","No completed orders were found.") %>
			</li>
		<% end_if %>
		<li>
			<h4><% _t("INCOMPLETE","Incomplete Orders") %></h4>
		</li>
		<% if IncompleteOrders %>
			<% control IncompleteOrders %>
				<li>
					<a href="$Link" title="<% sprintf(_t("READMORE","Read more on Order #%s"),$ID) %>"><% _t("ORDER","Order #") %>{$ID}</a> ($Created.Nice)
				</li>
			<% end_control %>
		<% else %>
			<li>
				<% _t("NOINCOMPLETE","No incomplete orders were found.") %>
			</li>
		<% end_if %>
	</ul>
	$MemberForm
</div>
