<% require css("silvershop/core: client/dist/css/checkout.css") %>

<div class="silvershop-checkout silvershop-checkout--create-account">
    <h1 class="silvershop-checkout__title">$Title</h1>
    <div class="silvershop-checkout__content silvershop-typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <div class="silvershop-checkout__create-account">
        $Form
    </div>
</div>
