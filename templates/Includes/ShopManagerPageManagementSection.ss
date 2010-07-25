<h3>review basic options</h3>
<p>Below is a list of options for reviewing orders:</p></p>
<div class="enterOrderNumber"><label>enter order number: <input name="ShopManagerPageOrderID" id="ShopManagerPageOrderID" /></label></div>
<ul id="ShopManagerPageOptionList">
	<li><a href="{$Link}testorderreceipt">check email receipt</a></li>
	<li><a href="{$Link}teststatusupdatemail">check status update receipt</a></li>
	<li><a href="{$Link}showorder">view order details</a></li>
	<li><a href="{$Link}getorderdetailsforadmin">show order debug information</a></li>
</ul>

<h3>Other options</h3>
<ul id="ShopManagerPageOtherOptions">
	<li><a href="{$Link}clearcompletecart">clear complete cart</a> - useful if you want to pretend to be a new customer to the site</li>
</ul>
<% if Order %>
	<% control Order %>
		<% include OrderInformation %>
	<% end_control %>
<% else %>
<h3>Last Orders</h3>
	<% if LastOrders %>
<p class="showHideNext"><a href="#">show now</a></p>
<ul id="ShopManagerPageLastOrders">
	<% control LastOrders %><li>#$ID, $Created.Nice, $Status, $Member.Firstname $Member.Surname, $Member.Email</li><% end_control %>
</ul>
	<% else %>
<p>There are no orders</p>
	<% end_if %>
<% end_if %>
