<div id="ProductNavigation">
 	<h3><% control Level(1) %><a href="$Link">$Title</a><% end_control %></h3>
 	<ul>
		<% control GroupsMenu %>
 	    		<% if Children %>
		  	    <li class="$LinkingMode"><a href="$Link" title="<% sprintf(_t("GOTOPAGE","Go to the %s page"),$Title.XML) %>" class="$LinkingMode levela"><span><em>$MenuTitle.XML</em></span></a>
  	    	<% else %>
	  			<li><a href="$Link" title="<% sprintf(_t("GOTOPAGE","Go to the %s page"),$Title.XML) %>" class="$LinkingMode levela"><span><em>$MenuTitle.XML</em></span></a>
			<% end_if %>
  			<% if LinkOrSection = section %>
  				<% if ChildGroups %>
					<ul>
						<% control ChildGroups %>
							<li><a href="$Link" title="<% sprintf(_t("GOTOPAGE","Go to the %s page"),$Title.XML) %>" class="$LinkingMode levelb">$MenuTitle.LimitCharacters(22)</a></li>
						<% end_control %>
					</ul>
		 		 <% end_if %>
			<% end_if %> 
		</li> 
 		<% end_control %>
 	</ul>
</div>