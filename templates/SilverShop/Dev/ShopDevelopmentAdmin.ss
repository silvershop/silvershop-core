<h3><%t SilverShop\Dev\ShopDevelopmentAdmin.CartTasks "Cart tasks" %></h3>
<ul>
    <li><a href="{$BaseHref}shoppingcart/clear"><%t SilverShop\Dev\ShopDevelopmentAdmin.ClearCart "Clear the current shopping cart" %></a></li>
    <li><a href="{$BaseHref}shoppingcart/debug"><%t SilverShop\Dev\ShopDevelopmentAdmin.DebugCart "Debug the shopping cart" %></a></li>
    <li><a href="{$BaseHref}dev/tasks/PopulateCartTask"><%t SilverShop\Dev\ShopDevelopmentAdmin.PopulateCart "Populate cart with some available products" %></a></li>
</ul>

<h3><%t SilverShop\Dev\ShopDevelopmentAdmin.BuildTasks "Build Tasks" %></h3>
<ul>
    <li><a href="{$BaseHref}dev/tasks/CartCleanupTask"><%t SilverShop\Dev\ShopDevelopmentAdmin.CartCleanup "Cleanup old carts" %></a>
    - <%t SilverShop\Dev\ShopDevelopmentAdmin.CartCleanupDesc "Remove abandoned carts." %></li>
    <li><a href="{$BaseHref}dev/tasks/RecalculateAllOrdersTask"><%t SilverShop\Dev\ShopDevelopmentAdmin.RecalculateOrders "Recalculate all orders" %></a>
    - <%t SilverShop\Dev\ShopDevelopmentAdmin.RecalculateOrdersDesc "Recalculate all order values. Warning: this will overwrite any historical values." %></li>
    <li><a href="{$BaseHref}dev/tasks/PopulateShopTask"><%t SilverShop\Dev\ShopDevelopmentAdmin.PopulateShop "Populate shop" %></a>
    - <%t SilverShop\Dev\ShopDevelopmentAdmin.PopulateShopDesc "Populate the shop with dummy products, categories, and other necessary pages." %></li>
</ul>

<h3><%t SilverShop\Dev\ShopDevelopmentAdmin.UnitTests "Unit Tests" %></h3>
<ul>
    <li><a href="{$BaseHref}dev/tests/module/$ShopFolder"><%t SilverShop\Dev\ShopDevelopmentAdmin.RunAllTests "Run all shop unit tests" %></a></li>
</ul>
