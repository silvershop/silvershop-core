<% control Member %>
<address class="addressSection" cellspacing="0" cellpadding="0" id="PurchaserAddressSection">
	<% if Name %>$Name<br /><% end_if %>
	<% if Address %>$Address<br/><% end_if %>
	<% if AddressLine2 %>$Address2<br /><% end_if %>
	<% if City %>$City<br /><% end_if %>
	<% if State %>$State<br /><% end_if %>
	<% if PostalCode %>$PostalCode<br /><% end_if %>
	<% if FullCountryName %>$FullCountryName<br /><% end_if %>
	<% if Phone %>$Phone<br /><% end_if %>
	<% if Email %>$Email<br /><% end_if %>
</address>
<% end_control %>
