<nav class="silvershop-product-nav">
    <h3 class="silvershop-product-nav__title"><% with $Level(1) %><a class="silvershop-product-nav__title-link" href="$Link">$Title</a><% end_with %></h3>
    <ul class="silvershop-product-nav__list">
        <% loop $GroupsMenu %>
            <% if $Children %>
                <li class="silvershop-product-nav__item silvershop-product-nav__item--$LinkingMode"><a href="$Link" title="<%t SilverShop\Includes\ProductMenu.GotoPageTitle "Go to the {Title} page" Title=$Title.XML %>" class="silvershop-product-nav__link silvershop-product-nav__link--$LinkingMode silvershop-product-nav__link--level-a"><span><em>$MenuTitle.XML</em></span></a>
            <% else %>
                <li class="silvershop-product-nav__item"><a href="$Link" title="<%t SilverShop\Includes\ProductMenu.GotoPageTitle "Go to the {Title} page" Title=$Title.XML %>" class="silvershop-product-nav__link silvershop-product-nav__link--$LinkingMode silvershop-product-nav__link--level-a"><span><em>$MenuTitle.XML</em></span></a>
            <% end_if %>
            <% if $LinkOrSection == 'section' %>
                <% if $ChildGroups %>
                    <ul class="silvershop-product-nav__sublist">
                        <% loop $ChildGroups %>
                            <li class="silvershop-product-nav__subitem"><a href="$Link" title="<%t SilverShop\Includes\ProductMenu.GotoPageTitle "Go to the {Title} page" Title=$Title.XML %>" class="silvershop-product-nav__sublink silvershop-product-nav__sublink--$LinkingMode silvershop-product-nav__sublink--level-b">$MenuTitle.LimitCharacters(22)</a></li>
                        <% end_loop %>
                    </ul>
                 <% end_if %>
            <% end_if %>
        </li>
        <% end_loop %>
    </ul>
</nav>
