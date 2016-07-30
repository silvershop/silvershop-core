<% require themedCSS(checkout,shop) %>
<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
    <div class="typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <div class="createaccount">
        $Form
    </div>
</div>
