<h3><% _t("CARTTASKS","Cart tasks") %></h3>
<p><a href="{$BaseHref}shoppingcart/clear"><% _t("","Clear the current shopping cart") %></a></p>
<p><a href="{$BaseHref}shoppingcart/debug"><% _t("","Debug the shopping cart") %></a></p>

<h3><% _t("BUILDTASKS","Build Tasks") %></h3>
<p>
	<a href="{$BaseHref}dev/tasks/CartCleanupTask"><% _t("CARTCLEANUP","Cleanup old carts") %></a> 
	- <% _t("CARTCLEANUPDESC","Remove abandoned carts.") %>
</p>

<p>
	<a href="{$BaseHref}dev/tasks/RecalculateAllOrdersTask"><% _t("RECALCULATEORDERS","Recalculate All Orders") %></a>
	- <% _t("RECALCULATEORDERSDESC","Recalculate all order values. Warning: this will overwrite any historical values.") %>
</p>

<h3><% _t("UNITTESTS","Unit Tests") %></h3>
<p><a href="{$BaseHref}dev/tests/module/$ShopFolder"><% _t("RUNALLTESTS","Run all shop unit tests") %></a></p>