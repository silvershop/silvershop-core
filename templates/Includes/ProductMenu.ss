<div id="Sidebar" class="typography">
	<div class="sidebarBox">
 		<h3>
			<% control Level(1) %>
				$Title
			<% end_control %>
  		</h3>
  		
  		<ul id="Menu2">
			<% control GroupsMenu %>
  	    		<% if Children %>
			  	    <li class="$LinkingMode"><a href="$Link" title="<% sprintf(_t("GOTOPAGE","Go to the %s page"),$Title.XML) %>" class="$LinkingMode levela"><span><em>$MenuTitle.XML</em></span></a>
	  	    	<% else %>
		  			<li><a href="$Link" title="<% sprintf(_t("GOTOPAGE","Go to the %s page"),$Title.XML) %>" class="$LinkingMode levela"><span><em>$MenuTitle.XML</em></span></a>
				<% end_if %>	  
	  		
	  			<% if LinkOrSection = section %>
	  				<% if ChildGroups %>
						<ul class="sub">
							<li>
								<ul class="roundWhite">
									<% control ChildGroups %>
										<li><a href="$Link" title="<% sprintf(_t("GOTOPAGE","Go to the %s page"),$Title.XML) %>" class="$LinkingMode levelb"><span><em>$MenuTitle.LimitCharacters(22)</em></span></a></li>
									<% end_control %>
								</ul>
							</li>
						</ul>
			 		 <% end_if %>
				<% end_if %> 
			</li> 
  			<% end_control %>
  		</ul>
		<div class="clear"></div>
	</div>
	<div class="sidebarBottom"></div>
	
	<div class="sidebarBox cart">
		<% include Cart %>
	</div>
	<div class="sidebarBottom"></div>
</div>