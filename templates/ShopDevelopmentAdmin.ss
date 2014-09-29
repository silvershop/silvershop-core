<h3><% _t("CARTTASKS","Cart tasks") %></h3>
<ul>
	<li><a href="{$BaseHref}shoppingcart/clear"><% _t("CLEARCART","Clear the current shopping cart") %></a></li>
	<li><a href="{$BaseHref}shoppingcart/debug"><% _t("DEBUGCART","Debug the shopping cart") %></a></li>
	<li><a href="{$BaseHref}dev/tasks/PopulateCartTask"><% _t("POPULATECART","Populate cart with some available products") %></a></li>
</ul>

<h3><% _t("BUILDTASKS","Build Tasks") %></h3>
<ul>
	<li><a href="{$BaseHref}dev/tasks/CartCleanupTask"><% _t("CARTCLEANUP","Cleanup old carts") %></a> 
	- <% _t("CARTCLEANUPDESC","Remove abandoned carts.") %></li>
	<li><a href="{$BaseHref}dev/tasks/DeleteOrdersTask"><% _t("DELETEORDERS","Delete All Orders") %></a> 
	- <% _t("DELETEORDERSDESC","Remove all orders, modifiers, and payments from the database.  (you need to put ?type=sql on the end)") %></li>
	<li><a href="{$BaseHref}dev/tasks/DeleteProductsTask"><% _t("DELETEPRODUCTS","Delete All Products") %></a>
	- <% _t("DELETEPRODUCTSDESC","Remove all products from the database.") %></li>
	<li><a href="{$BaseHref}dev/tasks/RecalculateAllOrdersTask"><% _t("RECALCULATEORDERS","Recalculate All Orders") %></a>
	- <% _t("RECALCULATEORDERSDESC","Recalculate all order values. Warning: this will overwrite any historical values.") %></li>
	<li><a href="{$BaseHref}dev/tasks/PopulateShopTask"><% _t("POPULATESHOP","Populate shop") %></a>
	- <% _t("POPULATESHOPDESC","Populate the shop with dummy products, categories, and other necessary pages.") %></li>
</ul>

<h3><% _t("UNITTESTS","Unit Tests") %></h3>
<ul>
	<li><a href="{$BaseHref}dev/tests/module/$ShopFolder"><% _t("RUNALLTESTS","Run all shop unit tests") %></a></li>
</ul>