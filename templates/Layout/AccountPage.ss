<% require themedCSS(account) %>
<div class="accountnav">
	<div class="nav">
		<h2>My Account</h2>
		<ul class="nav nav-list">
			<li class="$LinkingMode"><a href="$Link"><i class="icon icon-list"></i> Past Orders</a></li>
			<li class="$LinkingMode"><a href="$Link(editprofile)"><i class="icon icon-user"></i> Edit Profile</a></li>
			<li class="$LinkingMode"><a href="$Link(addressbook)"><i class="icon icon-book"></i> Address Book</a></li>
		</ul>
	</div>
	<div class="memberdetails">	
		<% control Member %>
			<dl>
				<dt>Name</dt><dd>$Name</dd>
				<dt>Email</dt><dd>$Email</dd>
				<dt>Member Since</dt> <dd>$Created.Nice</dd>
				<dt>Last Visit</dt> <dd>$LastVisited.Nice</dd>
				<dt>Number of orders</dt> <dd><% if PastOrders %>$PastOrders.Count<% else %>0<% end_if %></dd>
			</dl>
		<% end_control %>
	</div>
	<a></a>
	<a href="Security/logout" class="btn btn-inverted"><i class="icon icon-off"></i> Log Out</a>
</div>
<div class="typography accountcontent">
	$Content
</div>
<div class="clear"></div>