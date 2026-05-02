<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1 class="silvershop-pageTitle">$Title</h1>
<div id="Checkout">
    <div class="silvershop-typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <div class="silvershop-createaccount">
        $Form
    </div>
</div>
