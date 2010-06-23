<div id="Account">
	<div class="typography">
		<h2>$Title</h2>
		<% if Content %>
			$Content
		<% end_if %>
	</div>
	<div id="PastOrders">

		<h3><% _t("HISTORY","Your Order History") %></h3>
		<h4><% _t("COMPLETED","Completed Orders") %></h4>
		<% if CompleteOrders %>
		<ul>
			<% control CompleteOrders %>
				<li>
					<a href="$Link" title="<% sprintf(_t("READMORE","Read more on Order #%s"),$ID) %>"><% _t("ORDER","Order #") %>{$ID}</a> ($Created.Nice)
				</li>
			<% end_control %>
		</ul>
		<% else %>
			<p><% _t("NOCOMPLETED","No completed orders were found.") %></p>
		<% end_if %>
			<h4><% _t("INCOMPLETE","Incomplete Orders") %></h4>
		<% if IncompleteOrders %>
		<ul>
			<% control IncompleteOrders %>
				<li>
					<a href="$Link" title="<% sprintf(_t("READMORE","Read more on Order #%s"),$ID) %>"><% _t("ORDER","Order #") %>{$ID}</a> ($Created.Nice)
				</li>
			<% end_control %>
		</ul>
		<% else %>
			<p><% _t("NOINCOMPLETE","No incomplete orders were found.") %></p>
		<% end_if %>
	</div>
	$MemberForm
</div>
