<div id="ProductNavigation">
    <h3><% with $Level(1) %><a href="$Link">$Title</a><% end_with %></h3>
    <ul>
        <% loop $GroupsMenu %>
            <% if $Children %>
                <li class="$LinkingMode"><a href="$Link" title="<%t SilverShop\Includes\ProductMenu.GotoPageTitle "Go to the {Title} page" Title=$Title.XML %>" class="$LinkingMode levela"><span><em>$MenuTitle.XML</em></span></a>
            <% else %>
                <li><a href="$Link" title=<%t SilverShop\Includes\ProductMenu.GotoPageTitle "Go to the {Title} page" Title=$Title.XML %>" class="$LinkingMode levela"><span><em>$MenuTitle.XML</em></span></a>
            <% end_if %>
            <% if $LinkOrSection == 'section' %>
                <% if $ChildGroups %>
                    <ul>
                        <% loop $ChildGroups %>
                            <li><a href="$Link" title="<%t SilverShop\Includes\ProductMenu.GotoPageTitle "Go to the {Title} page" Title=$Title.XML %>" class="$LinkingMode levelb">$MenuTitle.LimitCharacters(22)</a></li>
                        <% end_loop %>
                    </ul>
                 <% end_if %>
            <% end_if %>
        </li>
        <% end_loop %>
    </ul>
</div>
