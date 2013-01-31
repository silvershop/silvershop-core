<% require themedCSS(order) %>
<div id="OrderInformation">
	<tr>
	<td><a href="$Link" >Order $ID</a></td>
	<td class="right">$Total.Nice $Currency</td><td class="price">$Created.Nice24</td>
				<td class="price">$Amount.Nice $Currency</td>
				<td class="price">$Status</td>
				
				
				
	
	<% if Notes %>
	
					<td>$Notes</td>

	<% end_if %>
	</tr>
</div>