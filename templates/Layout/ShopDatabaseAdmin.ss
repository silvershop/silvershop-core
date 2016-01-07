<h3><%t ShopDevelopmentAdmin.CartTasks "Cart tasks" %></h3>
<p><a href="{$BaseHref}shoppingcart/clear"><%t ShopDevelopmentAdmin.ClearCart "Clear the current shopping cart" %></a></p>
<p><a href="{$BaseHref}shoppingcart/debug"><%t ShopDevelopmentAdmin.DebugCart "Debug the shopping cart" %></a></p>

<h3><%t ShopDevelopmentAdmin.BuildTasks "Build Tasks" %></h3>
<p>
    <a href="{$BaseHref}dev/tasks/CartCleanupTask"><%t ShopDevelopmentAdmin.CartCleanup "Cleanup old carts" %></a>
    - <%t ShopDevelopmentAdmin.CartCleanupDesc "Remove abandoned carts." %>
</p>
<p>
    <a href="{$BaseHref}dev/tasks/RecalculateAllOrdersTask"><%t ShopDevelopmentAdmin.RecalculateOrders "Recalculate all orders" %></a>
    - <%t ShopDevelopmentAdmin.RecalculateOrdersDesc "Recalculate all order values. Warning: this will overwrite any historical values." %>
</p>

<h3><%t ShopDevelopmentAdmin.UnitTests "Unit Tests" %></h3>
<p><a href="{$BaseHref}dev/tests/module/$ShopFolder"><%t ShopDevelopmentAdmin.RunAllTests "Run all shop unit tests" %></a></p>
