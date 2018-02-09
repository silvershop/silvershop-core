<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
    <div class="typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    $OrderForm
</div>
