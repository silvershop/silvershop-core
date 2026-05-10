<% require css("silvershop/core: client/dist/css/checkout.css") %>

<div class="silvershop-checkout silvershop-checkout--membership">
    <h1 class="silvershop-checkout__title">$Title</h1>
    <div class="silvershop-checkout__content silvershop-typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <div class="silvershop-checkout__membership">
        $Form
    </div>
    <div class="silvershop-checkout__login">
        <h2 class="silvershop-checkout__login-title"><%t SilverStripe\Security\Security.LOGIN 'Log In' %></h2>
        $LoginForm
    </div>
</div>
