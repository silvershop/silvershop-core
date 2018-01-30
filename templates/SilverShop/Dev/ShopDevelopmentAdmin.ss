<h3><%t ShopDevelopmentAdmin.CartTasks "Cart tasks" %></h3>
<ul>
    <li><a href="{$BaseHref}shoppingcart/clear"><%t ShopDevelopmentAdmin.ClearCart "Clear the current shopping cart" %></a></li>
    <li><a href="{$BaseHref}shoppingcart/debug"><%t ShopDevelopmentAdmin.DebugCart "Debug the shopping cart" %></a></li>
    <li><a href="{$BaseHref}dev/tasks/PopulateCartTask"><%t ShopDevelopmentAdmin.PopulateCart "Populate cart with some available products" %></a></li>
</ul>

<h3><%t ShopDevelopmentAdmin.BuildTasks "Build Tasks" %></h3>
<ul>
    <li><a href="{$BaseHref}dev/tasks/CartCleanupTask"><%t ShopDevelopmentAdmin.CartCleanup "Cleanup old carts" %></a>
    - <%t ShopDevelopmentAdmin.CartCleanupDesc "Remove abandoned carts." %></li>
    <li><a href="{$BaseHref}dev/tasks/RecalculateAllOrdersTask"><%t ShopDevelopmentAdmin.RecalculateOrders "Recalculate all orders" %></a>
    - <%t ShopDevelopmentAdmin.RecalculateOrdersDesc "Recalculate all order values. Warning: this will overwrite any historical values." %></li>
    <li><a href="{$BaseHref}dev/tasks/PopulateShopTask"><%t ShopDevelopmentAdmin.PopulateShop "Populate shop" %></a>
    - <%t ShopDevelopmentAdmin.PopulateShopDesc "Populate the shop with dummy products, categories, and other necessary pages." %></li>
</ul>

<h3><%t ShopDevelopmentAdmin.UnitTests "Unit Tests" %></h3>
<ul>
    <li><a href="{$BaseHref}dev/tests/module/$ShopFolder"><%t ShopDevelopmentAdmin.RunAllTests "Run all shop unit tests" %></a></li>
</ul>
