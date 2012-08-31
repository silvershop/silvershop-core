<h3><% _t("CARTTASKS","Cart tasks") %></h3>
<p><a href="{$BaseHref}shoppingcart/clear"><% _t("CLEARCART","Clear the current shopping cart") %></a></p>
<p><a href="{$BaseHref}shoppingcart/debug"><% _t("DEBUGCART","Debug the shopping cart") %></a></p>
<p><a href="{$BaseHref}dev/shop/populatecart"><% _t("POPULATECART","Populate cart with some available products") %></a></p>

<h3><% _t("BUILDTASKS","Build Tasks") %></h3>
<p>
	<a href="{$BaseHref}dev/tasks/CartCleanupTask"><% _t("CARTCLEANUP","Cleanup old carts") %></a> 
	- <% _t("CARTCLEANUPDESC","Remove abandoned carts.") %>
</p>

<p>
	<a href="{$BaseHref}dev/tasks/DeleteOrdersTask"><% _t("DELETEORDERS","Delete All Orders") %></a> 
	- <% _t("DELETEORDERSDESC","Remove all orders, modifiers, and payments from the database.") %>
</p>
<p>
	<a href="{$BaseHref}dev/tasks/DeleteProductsTask"><% _t("DELETEPRODUCTS","Delete All Products") %></a>
	- <% _t("DELETEPRODUCTSDESC","Remove all products from the database.") %>
</p>
<p>
	<a href="{$BaseHref}dev/tasks/RecalculateAllOrdersTask"><% _t("RECALCULATEORDERS","Recalculate All Orders") %></a>
	- <% _t("RECALCULATEORDERSDESC","Recalculate all order values. Warning: this will overwrite any historical values.") %>
</p>

<h3><% _t("UNITTESTS","Unit Tests") %></h3>
<p><a href="{$BaseHref}dev/tests/module/$ShopFolder"><% _t("RUNALLTESTS","Run all shop unit tests") %></a></p>