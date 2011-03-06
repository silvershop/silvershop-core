<% if ShippingAddress %> <% control ShippingAddress %>
<address class="addressSection" id="ShippingAddressAddressSection">
	<% if ShippingName %>$ShippingName<br /><% end_if %>
	<% if ShippingAddress %>$ShippingAddress<br /><% end_if %>
	<% if AddressLine2 %>$ShippingAddress2<br /><% end_if %>
	<% if ShippingCity %>$ShippingCity<br /><% end_if %>
	<% if ShippingState %>$ShippingState<br /><% end_if %>
	<% if ShippingPostalCode %>$ShippingPostalCode<br /><% end_if %>
	<% if ShippingFullCountryName %>$ShippingFullCountryName $ShippingCountry<br /><% end_if %>
	<% if ShippingPhone %>$ShippingPhone<% end_if %>
</address>
<% end_control %>
<% end_if %>
