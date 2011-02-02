<div id="AccountPageMessage" class="message">$Message</div>
<% if CurrentOrder %>
	<% control CurrentOrder %><% include Order %><% end_control %>
		<div id="SendCopyOfReceipt"><p><a href="{$Link}sendreceipt/$CurrentOrder.ID/">send a copy of receipt to $CurrentOrder.Member.Email</a></p></div>
		<div id="PaymentForm" class="typography">$PaymentForm</div>
		<div id="CancelForm" class="typography">$CancelForm</div>
<% else %>
	<% if AllMemberOrders %>
	<div id="PastOrders">
		<h3 class="formHeading"><% _t("HISTORY","Your Order History") %></h3>
		<% control AllMemberOrders %>
		<h4>$Heading</h4>
		<ul>
			<% control Orders %><li><a href="$Link">$Title</a></li><% end_control %>
		</ul>
		<% end_control %>
	</div>
	<% else %>
	<p><% _t("NOHISTORY","You dont have any saved orders.") %></p>
	<% end_if %>
	<div id="MemberForm" class="typography">$MemberForm</div>
<% end_if %>
